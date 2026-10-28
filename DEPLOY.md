# Deploying crqr.in (CloudPanel)

Server: `46.224.184.63` · Site user: `crqr` · App folder: `/home/crqr/htdocs/crqr.in`

Built frontend files (`public/build`) are committed, so the server doesn't need Node.
**Before every push, run `npm run build` locally and commit the result.**

---

## One-time setup

### 1. DNS (at your domain registrar)
| Type | Name | Value |
|---|---|---|
| A | `@` | `46.224.184.63` |
| CNAME | `www` | `crqr.in` |

Delete any other A/AAAA records for `@` and `www` (for example the registrar's parking page).
Check it with `nslookup crqr.in`; it should return `46.224.184.63`.

### 2. CloudPanel → Sites → crqr.in
- **Settings**
  - PHP Version: **PHP 8.5**
  - Root Directory: **`crqr.in/public`**
  - Save
- **Databases → Add Database**: name `crqr`, user `crqr`, generate a password and save it somewhere safe.
- **SSL/TLS → Actions → New Let's Encrypt Certificate** for `crqr.in` and `www.crqr.in`. Do this after DNS points to the server.
- **SSH/FTP**: set a password for the `crqr` user, or paste your public SSH key under Settings → SSH Keys.

### 3. Code (SSH in as the site user)
```bash
ssh crqr@46.224.184.63

cd ~/htdocs/crqr.in
ls -la        # should only hold CloudPanel's placeholder files. Stop and check if anything else is there.
rm -rf ~/htdocs/crqr.in/* ~/htdocs/crqr.in/.[!.]*

git clone https://github.com/deovrat247/crqr.git .
```
If the GitHub repo is **private**, create a deploy key first:
```bash
ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ""
cat ~/.ssh/id_ed25519.pub   # GitHub → repo → Settings → Deploy keys → Add (read-only)
git clone git@github.com:deovrat247/crqr.git .
```

### 4. Environment
```bash
cp .env.production.example .env
nano .env                 # set DB_PASSWORD to the password from step 2
php8.5 -m | grep -E "^(gd|pdo_mysql)$"   # both must be listed
php8.5 $(command -v composer) install --no-dev --optimize-autoloader
php8.5 artisan key:generate
bash deploy.sh
```
Open https://crqr.in. You should see the home page; sign up and shorten a link to test.

---

## Every later deploy
Locally:
```bash
npm run build
git add -A && git commit -m "..." && git push
```
On the server:
```bash
ssh crqr@46.224.184.63 "cd ~/htdocs/crqr.in && bash deploy.sh"
```

## Troubleshooting
- **500 error:** `tail -50 ~/htdocs/crqr.in/storage/logs/laravel-*.log`
- **Styles missing:** `public/build` wasn't committed. Run `npm run build` locally, commit and push.
- **Links show `http://`:** check that `APP_URL=https://crqr.in` and `APP_ENV=production`, then run `php8.5 artisan optimize`.
- **Permission errors:** `chmod -R ug+rw storage bootstrap/cache`
