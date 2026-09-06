# Copy this file to scripts/secrets.ps1 and fill in the real cPanel values.
# secrets.ps1 is gitignored - never commit it.
#
#   Copy-Item scripts\secrets.example.ps1 scripts\secrets.ps1

# Public domain of the live site. Drives canonical tags and all JSON-LD urls.
$SITE_URL = "https://samvedna.node2begin.com"

# Where the build reads blog/doctor/testimonial content from, as reachable FROM
# THIS MACHINE at build time. The static export bakes in whatever it returns.
#   - First deploy (site not live yet): run the local PHP server and use
#     http://127.0.0.1:8000  (npm run dev:php)
#   - After the site is live: use the live domain, so a rebuild picks up
#     everything the client has published since.
$PHP_ADMIN_URL = "http://127.0.0.1:8000"

# MySQL, as seen from the cPanel server. PHP connects locally, so DB_HOST is
# almost always "localhost" and the names carry the cPanel account prefix.
$DB_HOST = "localhost"
$DB_PORT = "3306"
$DB_NAME = "extrabit_samvedna"
$DB_USER = "extrabit_samvedna"
$DB_PASS = "CHANGE_ME"
