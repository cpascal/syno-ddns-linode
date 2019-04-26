# Linode DDNS script for Synology DSM

If you're maintaining your domain on Linode, you can use this script to manage DDNS.

## Installation Guide
1. Connect to your DSM using SSH terminal client (Putty on Windows)
2. Be root
<pre>
sudo su -
[input your password if the password is asked]
</pre>
3. Add the following contents to /etc.defaults/ddns_provider.conf file.
<pre>
[Linode]
        modulepath=/usr/syno/bin/ddns/linode.php
        queryurl=Linode
</pre>
4. Download linode.php to /usr/syno/bin/ddns
<pre>
cd /usr/syno/bin/ddns
curl -O https://raw.githubusercontent.com/cpascal/syno-ddns-linode/master/linode.php
chmod 755 linode.php
</pre>
5. Open the DSM Control Panel, External Access, DDNS tab.
6. Click 'Add'
<pre>
Service provider: Linode
Hostname: [your DDNS hostname].[your domain]
Username/Email: [your domain secret API key]
Password/Key: [your domain secret API key]
</pre>
7. Click 'OK'

## Reference
* https://forum.synology.com/enu/viewtopic.php?t=70027
