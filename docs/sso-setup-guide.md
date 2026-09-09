# Setting up single sign-on — step by step

Plugin v1.76.2. Screen: **WordPress admin → BlueWorx → Single sign-on**.

## Before you start

Ask whoever runs your identity provider for:

1. **Which provider** — Microsoft Entra, Google Workspace, Okta, or any other OpenID Connect provider.
2. **Tenant ID** (Entra) or **provider domain** (Okta, e.g. `example.okta.com`). Google needs neither. Anything else: the sign-in service address.
3. **A client ID and client secret** from an app registration they create for this site.
4. Ask them to **register the site's return address** against that app. The exact address is shown on the settings screen — copy it from there.

Default return address: `https://yoursite.com/?blueworx_sso=callback`

> Google will not accept a return address on a plain `http://` site unless it is `localhost`. If you are testing on staging, staging must be on **https**.

## Step 1 — Turn the feature on

BlueWorx → Enhancements → switch on **Single sign-on**. The settings page then appears in the menu.

## Step 2 — Fill in the connection

On BlueWorx → Single sign-on:

| Field | What to put in |
|---|---|
| Provider | Pick yours from the list |
| Tenant ID / Your provider domain | Only shown for Entra and Okta |
| Identity provider address | Only for "Any OpenID Connect provider" |
| Client ID | From the app registration |
| Client secret | From the app registration (write-only — it is never shown again) |

Save. The **Connection** box at the bottom tells you whether the site can reach the provider.

## Step 3 — Give the provider the return address

Copy **"Give your provider this address"** from the settings screen and have it registered against the app. It must match exactly.

## Step 4 — Decide your two policy choices

- **Only these email domains may get an account** — e.g. `yourcompany.com`. Leave it blank and, with Google, *anyone with a Google account anywhere* can join. Fill this in.
- **Let the joining button create an account** — off by default. Turn it on only if you want self-service joining.
- **Role for new accounts** — Subscriber unless you have a reason. Administrator is never offered.

Signing in never creates an account, whatever these are set to.

## Step 5 — Put the buttons on the site

- The **sign in** button is added to the WordPress login screen automatically.
- Anywhere else: `[blueworx_sso_button]` to sign in, `[blueworx_sso_button intent="register"]` to join.

## Step 6 — Test it

1. Open the site's login screen in a private browser window.
2. Click the sign-in button, sign in with a work account.
3. You should land back on the site, signed in.
4. Check **Recent sign-ins** at the bottom of the settings screen — it logs the last 20 attempts and the reason for any failure.

## Step 7 — Tighten up (optional, after a successful test)

Under **Advanced**:

- **Sign people out of the provider too** — otherwise logging out of WordPress only ends the WordPress session, and the next click walks straight back in.
- **Hide the WordPress password form** — only available once an administrator has signed in successfully through the provider. Admins can still reach the password form with `?blueworx-password=1`.

## If something goes wrong

Everything is logged server-side; the visitor only ever sees one generic message. Check **Recent sign-ins** for the real reason.

Most common causes:

- Return address does not match exactly what the provider has registered.
- Client secret expired or mistyped.
- Email not verified at the provider — unverified emails are never linked to an account.
- Google + `http://` staging site.
