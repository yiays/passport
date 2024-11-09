/*
  Edge logic for creating, fetching, and authorizing Passport accounts
*/

import { checkUsername, getProfileByToken } from "./account_controls";
import { AuthPaths, GenericAuth } from "./auth_methods";

export default {
  async fetch(request, env, ctx): Promise<Response> {
    const path = (new URL(request.url)).pathname.replace(/^\/api/, '');
    const splitPath = path.split('/');
    let params:URLSearchParams|FormData;
    if(request.method == 'GET') {
      params = (new URL(request.url)).searchParams;
    }else if(request.method == 'POST') {
      params = await request.formData();
    }else{
      return new Response('Not Implemented', {status: 501});
    }

    if(path == '/') {
      const token = params.get('token');
      if(typeof token == 'string') {
        const result = await GenericAuth.validate_session(env, token);
        if(result === false)
          return new Response('Token is invalid', {status: 401});
        return Response.json(result);
      }

      const username = params.get('username');
      if(typeof username == 'string')
        return Response.json(checkUsername(env, username));
      
      return new Response('Bad request', {status: 400});
    }else if(splitPath[1] == 'account'){
      const token = params.get('token');
      if(typeof token == 'string') {
        const profile = await getProfileByToken(env, token);
        if(profile === false) return new Response('Token is invalid', {status: 401});
        return Response.json(profile);
      }
      
      return new Response('Bad request', {status: 400});
    }else if(splitPath[1] in AuthPaths){
      return AuthPaths[splitPath[1]].handler(env, splitPath, params);
    }else{
      return new Response('File not found', {status:404});
    }
  },
} satisfies ExportedHandler<Env>;