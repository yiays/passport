/*
  Methods for managing your profile
*/

import { GenericAuth } from "./auth_methods";

export interface Profile {
  username:string,
  email:string,
  verified:boolean
}

export async function getProfiles(env:Env):Promise<{[id:string]:Profile}> {
  return await env.STORE.get<{[id:string]:Profile}>(`profiles`, 'json') || {};
}
export async function getProfileByToken(env:Env, token:string): Promise<Profile|false> {
  const username = await GenericAuth.validate_session(env, token);
  if(username) {
    return getProfileByUsername(env, username);
  }
  return false;
}
export async function getProfileByUsername(env:Env, username:string): Promise<Profile|false> {
  const profiles = await getProfiles(env);
  if(profiles && username.toLowerCase() in profiles) {
    return profiles[username.toLowerCase()];
  }
  return false;
}
export async function getProfileByEmail(env:Env, email:string): Promise<Profile|false> {
  const profiles = await getProfiles(env);
  for (const username in profiles) {
    if (profiles[username].email.toLowerCase() == email.toLowerCase()) {
      return profiles[username];
    }
  }
  return false;
}

interface CheckOptions {
  rulesonly?:boolean,
  exists?:boolean
}

export async function checkUsername(env:Env, username:string, options:CheckOptions={}): Promise<true|string> {
  // Validate the username
  // Rules checks
  if(!username.match(/[\w\d]{3,15}/))
    return "Username must be between 3 and 15 alphanumeric characters. No spaces or symbols.";
  if(options.rulesonly)
    return true;
  // Database checks
  const profiles = await getProfiles(env);
  if(username.toLowerCase() in profiles) {
    if(options.exists) return true;
    return "This username is taken.";
  }
  if(options.exists) return "Username not found.";
  return true;
}

export async function checkEmail(env:Env, email:string, options:CheckOptions={}): Promise<true|string> {
  // Validate the email address
  // Rules checks
  if(!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/))
    return "This email address appears to be invalid.";
  if(options.rulesonly)
    return true;
  // Database checks
  const profiles = Object.values(await getProfiles(env));
  const emails = profiles.map((p) => p.email.toLowerCase() || '');
  if(emails.includes(email.toLowerCase())) {
    if(options.exists) return true;
    return "This email address is taken.";
  }
  if(options.exists) return "Email address not found.";
  return true;
}