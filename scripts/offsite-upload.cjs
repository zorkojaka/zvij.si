#!/usr/bin/env node
// Naloži eno datoteko v S3-združljivo shrambo (Hetzner Object Storage) s podpisom SigV4.
// Brez odvisnosti: curl 7.81 na tem strežniku S3 podpisuje napačno (SignatureDoesNotMatch).
// Uporaba: R2_* v okolju (glej ~/.config/zvij-offsite.env)
//   node offsite-upload.cjs <datoteka> <ključ>          naloži
//   node offsite-upload.cjs --get <ključ> <datoteka>    prenese (za preverbo obnove)
'use strict';
const crypto = require('node:crypto');
const fs = require('node:fs');

const args = process.argv.slice(2);
const get = args[0] === '--get';
const [file, key] = get ? [args[2], args[1]] : args;
const { R2_ACCESS_KEY_ID: id, R2_SECRET_ACCESS_KEY: secret, R2_BUCKET: bucket, R2_ENDPOINT: endpoint, R2_REGION: region } = process.env;
if (!file || !key || !id || !secret || !bucket || !endpoint || !region) {
  console.error('Uporaba: R2_* v okolju; offsite-upload.cjs <datoteka> <ključ>');
  process.exit(2);
}

const sha = d => crypto.createHash('sha256').update(d).digest('hex');
const hmac = (k, d) => crypto.createHmac('sha256', k).update(d).digest();

async function request(method, body) {
  const url = new URL(`${endpoint.replace(/\/$/, '')}/${bucket}/${key.split('/').map(encodeURIComponent).join('/')}`);
  const now = new Date().toISOString().replace(/[:-]|\.\d{3}/g, '');
  const date = now.slice(0, 8);
  const hash = sha(body || '');
  const headers = { host: url.host, 'x-amz-content-sha256': hash, 'x-amz-date': now };
  const signed = Object.keys(headers).sort();
  const canonical = [method, url.pathname, '', ...signed.map(h => `${h}:${headers[h]}`), '', signed.join(';'), hash].join('\n');
  const scope = `${date}/${region}/s3/aws4_request`;
  const toSign = ['AWS4-HMAC-SHA256', now, scope, sha(canonical)].join('\n');
  const kSign = ['s3', 'aws4_request'].reduce((k, d) => hmac(k, d), hmac(hmac(`AWS4${secret}`, date), region));
  const signature = crypto.createHmac('sha256', kSign).update(toSign).digest('hex');
  return fetch(url, {
    method,
    body,
    headers: {
      ...headers,
      authorization: `AWS4-HMAC-SHA256 Credential=${id}/${scope}, SignedHeaders=${signed.join(';')}, Signature=${signature}`,
    },
  });
}

(async () => {
  const body = get ? undefined : fs.readFileSync(file);
  // Hetzner občasno vrne 403 AccessDenied tudi na pravilen podpis — ponovi.
  for (let attempt = 1; attempt <= 8; attempt++) {
    const res = await request(get ? 'GET' : 'PUT', body);
    if (res.ok) {
      if (get) {
        fs.writeFileSync(file, Buffer.from(await res.arrayBuffer()));
        console.log(`preneseno: ${key} -> ${file}`);
      } else {
        console.log(`naloženo: ${key} (${body.length} B)`);
      }
      return;
    }
    const text = await res.text();
    if (attempt === 8 || !/AccessDenied|SlowDown|InternalError/.test(text)) {
      console.error(`napaka ${res.status}: ${text.slice(0, 200)}`);
      process.exit(1);
    }
    await new Promise(r => setTimeout(r, attempt * 1000));
  }
})();
