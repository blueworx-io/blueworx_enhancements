# SSO with PSA — worldsquashofficiating.com

Provider: PSA (account.psasquashtour.com). Plugin v1.76.2.
Source: PSA Single Sign-On (SSO) Integration Guide.

## What to request from PSA

Contact: harry.mattocks@psasquashtour.com

Ask for:

1. **Client ID and Client Secret** for a **confidential** client (server-side — WordPress can hold a secret).
2. **Registration of this return address**, exactly:
   `https://worldsquashofficiating.com/?blueworx_sso=callback`
   If their system will not accept a query string in a redirect URI, ask what form they can register and put that in *Advanced → Return address registered with the provider*.
3. **Whether every PSA account has a verified email.** The plugin only links accounts with `email_verified: true`. Any PSA user without it will be turned away.

RS256 no longer needs asking — see below.

## Settings to enter

| Field | Value |
|---|---|
| Provider | Any OpenID Connect provider |
| Identity provider address | `https://account.psasquashtour.com` |
| Client ID | from PSA |
| Client secret | from PSA |
| Scope (Advanced) | `openid email profile` — leave as default |
| Proof key (PKCE) | Leave on "Use it when the provider supports it" |

Leave all endpoint overrides blank — PSA publishes a discovery document at
`https://account.psasquashtour.com/.well-known/openid-configuration`, so the plugin
finds the endpoints itself. The **Connection** box on the settings screen confirms this.

## Policy choices — different from the generic advice

**Leave "Only these email domains may get an account" BLANK.**

The generic warning about this field is about Google, where an open door means the whole
world. PSA is a closed system: only people PSA has an account for can get through it at
all, and their emails are on every domain imaginable. Restricting by domain here would
lock out nearly every legitimate referee.

- **Let the joining button create an account** — turn ON if you want PSA members to
  self-serve. Signing in alone never creates an account.
- **Role for new accounts** — Subscriber (or whatever role your courses need).

## Verified against PSA's live discovery document (2026-09-02)

Read from `https://account.psasquashtour.com/.well-known/openid-configuration`.

- **Signing: `id_token_signing_alg_values_supported: ["RS256"]`** — RS256 and nothing else.
  Exactly what this plugin requires. Confirmed, no need to ask PSA.
- **Token auth: `client_secret_basic`, `client_secret_post`** — both supported by the plugin.
- **`email_verified` is in `claims_supported`** — the linking rule will work.
- **PKCE: no `code_challenge_methods_supported` key.** PSA does not advertise PKCE, so
  leave *Proof key (PKCE)* on **"Use it when the provider supports it"**. Setting it to
  "Always use it" would very likely break sign-in.
- **Sign-out: no `end_session_endpoint`.** PSA has `/oauth/revoke`, which is not the same
  thing. So *Sign people out of the provider too* has nothing to call — the plugin checks,
  finds nothing, and lets the normal WordPress sign-out finish. Harmless, but the option
  will not actually do anything. Leave it off.
- **Extra claims PSA returns:** `referee_id` and `newly_registered` alongside `department`,
  `set_up` and `beta_access` — `referee_id` may be worth mapping to a user field later.

## Things PSA's guide does not cover

- **Token refresh** — not needed here. WordPress keeps its own session after sign-in; the
  plugin does not hold PSA tokens.

## Useful claims PSA returns

`email`, `email_verified`, `name`, `given_name`, `family_name`, `preferred_username`,
`picture`, `phone_number`, plus PSA's own `department`, `set_up` and `beta_access`.
