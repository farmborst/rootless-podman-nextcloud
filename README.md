# rootless-podman-nextcloud
[![License][license]](LICENSE)

## Setup Procedure
 * make sure your system fulfils the general prerequisites for a rootless podman setup (see below)
 * clone this git repository
    ```bash
    $ git clone https://github.com/farmborst/rootless-podman-nextcloud.git
    ```
 * create and customize secrets file for defining passwords and sensitive data inside cloned git dir
    ```bash
    NEXTCLOUD_TRUSTED_DOMAINS='"10.8.0.2 192.168.1.2"' # IPs or domains you will be using to access nc
    MAIL_FROM_ADDRESS=somegmail.cron                   # mail address nc will use to inform you about stuff
    MAIL_DOMAIN=gmail.com                              # details for nc to send mails (example using gmail)
    SMTP_HOST=smtp.gmail.com                           # details for nc to send mails (example using gmail)
    SMTP_PORT=587                                      # details for nc to send mails (example using gmail)
    SMTP_AUTHTYPE=1                                    # details for nc to send mails (example using gmail)
    SMTP_NAME=somegmail.cron@gmail.com                 # details for nc to send mails (example using gmail)
    SMTP_PASSWORD=tokenpassword                        # details for nc to send mails (example using gmail)
    MYSQL_PASSWORD=strongpassword
    NEXTCLOUD_ADMIN_PASSWORD=strongpassword
    COLLABORA_PASSWORD=strongpassword
    REDIS_PASSWORD=strongpassword
    ```
* setup rootless podman nextcloud pod with all required containers
    * setup nextcloud podman pod
    ```bash
    $ bash nextcloud_podman_setup
    ```
    * go to configure nextcloud IP or domain (NEXTCLOUD_TRUSTED_DOMAINS) with your preferred browser and test
        * if everything works --> start systemd setup
        * else 
* setup systemd user unitfile for nextcloud pod
    ```bash
    $ bash nextcloud_systemd_setup
    ```

#### Get rid of warnings "Server has no maintenance window start time configured."
 * 'maintenance_window_start' => 3
 
#### Get rid of warning "Your installation has no default phone region set."
 * in the nextcloud directory open /config/config.php and append 'default_phone_region' => 'XX, e.g., XX=CH

#### Configure automatic execution of nextcloud background jobs
* for info on nextcloud Background Jobs see [nextcloud docs](https://docs.nextcloud.com/server/28/admin_manual/configuration_server/background_jobs_configuration.html)
* create systemd user unit files at ~/.config/systemd/user for regularly executing nextcloud Background Jobs on the host user running the rootless nextcloud pod
  * nextcloudcron.service
    ```systemd
    [Unit]
    Description=Nextcloud cron.php job

    [Service]
    ExecCondition=podman  exec -t -u www-data NextcloudContainer php -f /var/www/html/occ status -e
    ExecStart=podman  exec -t -u www-data NextcloudContainer php -f /var/www/html/cron.php
    KillMode=process
    ```
  * nextcloudcron.timer
    ```bash
    [Unit]
    Description=Run Nextcloud cron.php every 5 minutes

    [Timer]
    OnBootSec=5min
    OnUnitActiveSec=5min
    Unit=nextcloudcron.service

    [Install]
    WantedBy=timers.target
    ```
* enable and start the timer service
    ```bash
    $ systemctl --user enable --now nextcloudcron.timer
    ```
* according to [this](https://github.com/containers/podman/discussions/19426) you can ignore the error "conmon ... <error>: Unable to send container stderr message to parent Broken pipe" seen, when checking 
    ```bash
    $ systemctl --user status nextcloudcron.service
    ```

## update
* auto-updating the containers requires --label "io.containers.autoupdate=registry" when creating the containers and is then as easy as:
    ```bash
    $ podman auto-update
    ```

## General prerequisites for a rootless podman setup
* Install Podman, Cockpit and Cockpit-Podman and some networking and sub-uid/gid mapping utils
    ```bash
    # debian
    $ sudo apt-get install podman cockpit cockpit-podman slirp4netns uidmap
    # redhat
    $ sudo dnf install podman cockpit cockpit-podman slirp4netns shadow-utils
    ```
* podman version should be > 4
    ```bash
    $ podman --version
    ```
* enable running user containers/pods without active user session
    ```bash
    $ loginctl enable-linger $USER
    ```
* The useradd program usually automatically allocates 65536 UIDs for each added user. Check you have enough subuids and subgids assigned for the user starting the rootless podman containers/pods (depends on settings in shadow-utils/uidmap)
    ```bash
    # check
    $ cat /etc/subuid && cat /etc/subgid
    # in case not enough ids available
    sudo usermod --add-subuids 100000-165535 --add-subgids 100000-165535 $USER
    # make podman aware of changes
    podman system migrate
    ```

[license]: https://img.shields.io/badge/Lincense-GPL--3.0_license-orange