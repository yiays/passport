const tokenLifetime = 1000 * 60 * 60 * 24 * 90; // 90 days
const profileLifetime = 1000 * 60 * 60 * 3; // 3 hours

let passportBroadcast;
if('BroadcastChannel' in window) {
  passportBroadcast = new BroadcastChannel('passport_auth_event');

  passportBroadcast.onmessage = (ev) => {
    // Log event, refresh if the login is completed. Other code may want to override this.
    console.log(ev.data);
    if(ev.data.startsWith('profile='))
      location.reload();
  }
}

function passport_storeToken(token) {
  // Store token and announce it to any other open tabs
  const expires = new Date(Date.now() + tokenLifetime);
  document.cookie = `_passportToken=${token};domain=yiays.com;path=/;expires=${expires}`;
  if(passportBroadcast)
    passportBroadcast.postMessage(`token=${token}`);
}

function passport_storeProfile(profile) {
  // Store profile and announce it to any other open tabs
  const expires = new Date(Date.now() + profileLifetime);
  profile = JSON.stringify(profile);
  document.cookie = `_passportProfile=${profile};domain=yiays.com;path=/;expires=${expires}`;
  if(passportBroadcast)
    passportBroadcast.postMessage(`profile=${profile}`);
}

function passport_clearSession() {
  // Remove profile and token
  document.cookie = `_passportToken=;domain=yiays.com;path=/;max-age=0`;
  document.cookie = `_passportProfile=;domain=yiays.com;path=/;max-age=0`;
  if(passportBroadcast) {
    passportBroadcast.postMessage('token=');
    passportBroadcast.postMessage('profile=');
  }
}

function passport_getProfile(callback) {
  if(!document.cookie.includes('_passportToken='))
    return callback(false);
  if(document.cookie.includes('_passportProfile=')) {
    const rawprofile = document.cookie.split('; ').filter((s) => s.startsWith('_passportProfile='))[0].slice(17);
    return callback(JSON.parse(rawprofile));
  }
  const token = document.cookie.split('; ').filter((s) => s.startsWith('_passportToken='))[0].slice(15);
  $.ajax('/api/', {
    method: 'get',
    data: `token=${token}`,
    processData: false,
    statusCode: {
      400: () => {
        document.cookie = '_passportToken=;domain=yiays.com;path=/;max-age:0';
        callback(false);
      }
    },
    success: (data) => {
      if(data) {
        passport_storeProfile(data);
        callback(data);
      }
    },
  });
}

function passport_submitChallenge(challenge, callback) {
  if(document.cookie.includes('_passportToken=') && document.cookie.includes('_passportProfile=')) {
    const rawprofile = document.cookie.split('; ').filter((s) => s.startsWith('_passportProfile='))[0].slice(17);
    return callback(JSON.parse(rawprofile));
  }
  $.ajax('/api/generic', {
    method: 'get',
    data: `challenge=${challenge}`,
    processData: false,
    success: (data) => {
      if(data.success) {
        passport_storeToken(data.token);
        passport_storeProfile(data.profile);
        callback(data.profile);
      }else{
        callback(data.message);
      }
    },
    failure: () => {
      document.cookie = '_passportToken=;domain=yiays.com;path=/;max-age:0';
      callback("An unkown error has occured, please try again later.");
    },
  });
}