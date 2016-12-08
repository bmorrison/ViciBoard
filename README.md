# ViciBoard

ViciBoard is a free dashboard for ViciDIAL

Project homepage for more information: https://5gigahertz.com/viciboard.html

Quick installation instructions for impatient:

1. Clone this repositiory into your /htdocs directory of a web-serving system installed with the Vicibox Server ISO. You'll likely need to update your server's certificate bundle: zypper install ca-certificates{,-cacert,-mozilla}

2. Give global read-write permissions to the directory you cloned ViciBoard into.

3. Naviate to index.php using your web browser to the directory you cloned ViciBoard into.

4. Click "Settings" and make any changes as needed.

5. Add this crontab so that data is collected when when ViciBoard isn't running in a user's web broswer (assuming ViciBoard was cloned into "viciboard"): */5 * * * * /usr/bin/curl --silent http://localhost/viciboard/rolling_agent_stats.php > /dev/null 2>&1
