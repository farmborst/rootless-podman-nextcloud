# pod_nextcloud
![License][license]

## nextcloud Background Jobs
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

## update
* auto-updating the containers requires --label "io.containers.autoupdate=registry" when creating the containers and is then as easy as:
    ```bash
    $ podman auto-update
    ```

## some general prerequisites for getting a working rootless podman setup
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