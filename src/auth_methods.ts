/*
  Different methods for authentication available to the Client
*/

import { Resend } from "resend";
import { nanoid, customAlphabet as nanoid_custom } from "nanoid";
import { checkEmail, checkUsername, getProfileByEmail, getProfileByUsername, getProfiles, Profile } from "./account_controls";

const unknown_error = "An unknown error was encountered. Please try again later.";

interface ChallengeData {
  username:string,
  token:string,
  partialProfile:Partial<Profile>
}

export class GenericAuth {
  private static lifetime = 60 * 60 * 24 * 90; // 90 days
  // renew_threshold: How much time should pass before renewing the token
  private static renew_threshold = 60 * 60 * 24 * 30; // 30 days
  static challenge_instructions = "UNDEFINED";

  public static async handler(env:Env, splitPath:string[], params:URLSearchParams|FormData):Promise<Response> {
    // Get parameters and decide which stage of authentication we're in
    const token = params.get('token');
    const challenge = params.get('challenge');

    if(typeof token=='string') {
      // Edit profile
      return new Response('Action not yet supported');
    }
    if(typeof challenge == 'string') {
      // Validate challenge
      const result = await this.validate_challenge(env, challenge);
      if(result == false) return Response.json({success:false, message:"Challenge failed. Challenges expire after 3 hours."});
      return Response.json({success:true, ...result});
    }
    return new Response('Bad request', {status: 400});
  }

  static generate_token():string {
    // Generate a unique token for this client session
    return nanoid();
  }

  static generate_challenge():string {
    // Generate a unique challenge ID for this client
    return nanoid();
  }

  public static async create_session(env:Env, challengeData:ChallengeData):Promise<{profile:Profile, token:string}> {
    // The client is now trusted, store the token so they can reauth without logging in
    //WARN: This performs up to two write operations, limiting the daily login count to 500-1000
    //WARN: Factor in the delete in verify_challenge and it's 2-3 operaions, 333-500
    let profiles = await getProfiles(env);
    const profile = profiles[challengeData.username.toLowerCase()] || {};
    const newProfile = {...challengeData.partialProfile, ...profile};
    if(JSON.stringify(profile) != JSON.stringify(newProfile)) {
      profiles[challengeData.username.toLowerCase()] = newProfile;
      await env.STORE.put('profiles', JSON.stringify(profiles));
    }
    const authkey = `auth_${challengeData.token}`;
    await env.STORE.put(authkey, challengeData.username, {expirationTtl: this.lifetime});
    return {profile: newProfile, token: challengeData.token};
  }

  public static async validate_session(env:Env, token:string):Promise<false|string> {
    // Client claims to have an existing token. Confirm this.
    if(token.match(/[A-z0-9_-]{21}/)) {
      const authkey = `auth_${token}`;
      const result = await env.STORE.getWithMetadata(authkey);
      if(result.value) {
        //TODO: renew keys if they're close to expiring
        // There doesn't seem to be a way to check?
        return result.value;
      }
    }
    return false;
  }

  public static async create_challenge(env:Env, username:string, profile:Partial<Profile>={}):Promise<string> {
    // The client would like to earn trust, create a temporary token for use in a challenge
    const challenge = this.generate_challenge();
    const token = this.generate_token();
    const challengekey = `challenge_${challenge}`;
    const challengeData:ChallengeData = {username:username, token:token, partialProfile:profile}
    await env.STORE.put(
      challengekey,
      JSON.stringify(challengeData),
      {expirationTtl: (60 * 60 * 3)} // 3 hours
    );
    return challenge;
  }

  public static async validate_challenge(env:Env, challenge:string):Promise<false|{profile:Profile, token:string}> {
    // Check the client has passed the challenge
    if(challenge.match(/[A-z0-9_-]{21}/) || challenge.match(/\d{6}/)) {
      const challengekey = `challenge_${challenge}`;
      const result = await env.STORE.get<ChallengeData>(challengekey, 'json');
      if(result) {
        await env.STORE.delete(challengekey);
        return await this.create_session(env, result);
      }
    }
    return false;
  }
  
  public static async challenge(env:Env, username:string, email:string, register=false):Promise<boolean> {
    console.error("Called GenericAuth.challenge")
    return false;
  }
}

export class MagicEmailAuth extends GenericAuth {
  static challenge_instructions = "You have been sent an email. Click the link in the email to continue. Check your spam folder if you can't find it."

  public static async handler(env:Env, splitPath:string[], params:URLSearchParams|FormData):Promise<Response> {
    // Get parameters and decide which stage of authentication we're in
    const username = params.get('username');
    const email = params.get('email');
    
    if(typeof username == 'string' && typeof email == 'string') {
      // Register
      let result = await checkUsername(env, username);
      if(result !== true) return Response.json({success:false, message:result});
      result = await checkEmail(env, email);
      if(result !== true) return Response.json({success:false, message:result});
      const challengeResult = await this.challenge(env, username, email, true);
      if(challengeResult) return Response.json({success:true, message:this.challenge_instructions});
      return Response.json({success:false, message:unknown_error});
    }
    if(typeof email == 'string') {
      // Login
      const result = await getProfileByEmail(env, email);
      if(result == false) return Response.json({success:false, message:"Account not found."});
      const challengeResult = await this.challenge(env, result.username, result.email, false);
      if(challengeResult) return Response.json({success:true, message:this.challenge_instructions});
      return Response.json({success:false, message:unknown_error});
    }
    return new Response('Bad request', {status: 400});
  }

  public static async challenge(env:Env, username:string, email:string, register=false):Promise<boolean> {
    const resend = new Resend(env.RESEND_KEY);

    const partialProfile:Partial<Profile> = {...(register?{username:username}:{}), email:email, verified:true};
		const magic = await this.create_challenge(env, username, partialProfile);
    const magiclink = `https://passport.yiays.com/challenge/?challenge=${magic}`;

    // TODO: rate limit this per client
    const { data, error } = await resend.emails.send({
      from: "Passport for Merely Music, MemeDB, Yiays Blog <passport@yiays.com>",
      to: email,
      headers: {'X-Entity-Ref-ID': magic},
      subject: "Verify your Passport Account",
      html: `
      <img src="https://passport.yiays.com/img/icons/passport.png" width="128" height="128" alt="Logo for Passport" title="Passport">
      <h1>Passport</h1>
      <p>
        Click the following link (or copy and paste it into your browser) in order to continue signing
        in to Merely Music, MemeDB, or the Yiays.com blog.
      </p>
      <p><strong>Note: Be sure to open the link in the browser you would like to use.</strong></p>
      <p><a href="${magiclink}">${magiclink}</a></p>
      <p><i>If this wasn't you, you can safely ignore this email.</i></p>`,
			text: (
        "Copy and paste the following link into your browser in order to continue signing in to " +
        "Merely Music, MemeDB, or the Yiays.com blog.\n\n" +
        `${magiclink}\n\n` +
        "If this wasn't you, you can safely ignore this email."
      )
    });

    if(error) {
      console.error(error);
      return false;
    }
    return true;
  }
}

export class CodeEmailAuth extends MagicEmailAuth {
  static challenge_instructions = "You have been sent an email. Enter the code here. Check your spam folder if you can't find it."

  static generate_challenge():string {
    // Generate a unique challenge ID for this client
    return nanoid_custom('0123456789', 6)();
  }

  public static async challenge(env:Env, username:string, email:string, register=false):Promise<boolean> {
    const resend = new Resend(env.RESEND_KEY);

    const partialProfile:Partial<Profile> = {...(register?{username:username}:{}), email:email, verified:true};
		const magic = await this.create_challenge(env, username, partialProfile);

    // TODO: rate limit this per client
    const { data, error } = await resend.emails.send({
      from: "Passport for Merely Music, MemeDB, Yiays Blog <passport@yiays.com>",
      to: email,
      headers: {'X-Entity-Ref-ID': magic},
      subject: "Verify your Passport Account",
      html: `
      <img src="https://passport.yiays.com/img/icons/passport.png" width="128" height="128" alt="Logo for Passport" title="Passport">
      <h1>Passport</h1>
      <blockquote><i>Passport is your account for Merely Music, MemeDB, and the Yiays Blog.</i></blockquote>
      <p>
        Here is your verification code;
      </p>
      <p style="font-size:3em;background:grey;"><code style="margin:1em;background:lightgrey;color:black;">
        ${magic}
      </code></p>
      <p><i>If this wasn't you, you can safely ignore this email.</i></p>`,
			text: (
        "Here is your verification code;\n\n" +
        `${magic}\n\n` +
        "If this wasn't you, you can safely ignore this email."
      )
    });

    if(error) {
      console.error(error);
      return false;
    }
    return true;
  }
}

export const AuthPaths:{[id:string]:typeof GenericAuth} = {
  'generic': GenericAuth,
  'email': CodeEmailAuth,
  'magic': MagicEmailAuth
}