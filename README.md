# rootless-podman-nextcloud
[![License][license]](LICENSE)

## Setup Procedure
 1. make sure your system fulfils the general prerequisites for a rootless podman setup (see below)
 2. clone this git repository
    ```bash
    $ git clone https://github.com/farmborst/rootless-podman-nextcloud.git
    ```
 3. create and customize secrets file for defining passwords and sensitive data inside cloned git dir
    ```bash
    NEXTCLOUD_TRUSTED_DOMAINS='"10.8.0.2 192.168.1.2"'  # IPs or domains you will be using to access nc
    MAIL_FROM_ADDRESS='somegmail.cron'                  # mail address nc will use to inform you about stuff
    MAIL_DOMAIN='gmail.com'                             # details for nc to use your mail (example for using gmail)
    SMTP_HOST='smtp.gmail.com'                          # details for nc to use your mail (example for using gmail)
    SMTP_PORT='587'                                     # details for nc to use your mail (example for using gmail)
    SMTP_SECURE='tls'                                   # details for nc to use your mail (example for using gmail)
    SMTP_NAME='somegmail.cron@gmail.com'                # details for nc to use your mail (example for using gmail)
    SMTP_PASSWORD='tokenpassword'                       # details for nc to use your mail (example for using gmail)
    MYSQL_PASSWORD='strongpassword'
    MYSQL_ROOT_PASSWORD='strongpassword'
    NEXTCLOUD_ADMIN_PASSWORD='strongpassword'
    COLLABORA_PASSWORD='strongpassword'
    REDIS_PASSWORD='strongpassword'
    adminuser_mail='your@mail.com'                      # mail address for receiving mails from your nextcloud server
    ```
4. setup rootless podman nextcloud pod with all required containers 
    * check the file nextcloud_podman_setup for possible changes you may want to make, e.g., change the storage location of the data volume "ncv_data" to a dedicated disk or raid mount
    * setup nextcloud podman pod
    ```bash
    $ bash nextcloud_podman_setup
    ```
    * visit the configured nextcloud IP:port or domain (NEXTCLOUD_TRUSTED_DOMAINS) with your preferred browser
    * login as admin and setup your nextcloud server
        * if everything works --> continue with 5.
        * else start over
            * stop and remove pod, containers and volumes
                ```bash
                $ bash nextcloud_podman_remove
                ```
            * check 1. and restart with 3. --> check prerequisites and update configs (i.e. secrets file) to suite your needs
5. generate the systemd user unit files for the nextcloud pod and its containers and enable the service
    ```bash
    $ bash nextcloud_systemd_setup
    ```
6. generate the systemd user unit files on the host user (also running the rootless nextcloud pod) for regular and automatic execution of background jobs and enable the timer
    ```bash
    # copy systemd user unit files to to target directory
    $ cp nextcloudcron.service ~/.config/systemd/user
    $ cp nextcloudcron.timer ~/.config/systemd/user
    # reload systemd user daemon to let it find new unit files
    $ systemctl --user daemon-reload
    # start and enable systemd timer service for frequent execution of nextcloud background jobs
    $ systemctl --user enable --now nextcloudcron.timer
    ```
    * according to [here](https://github.com/containers/podman/discussions/19426) you can ignore the error "conmon ... <error>: Unable to send container stderr message to parent Broken pipe" seen, when checking 
        ```bash
        $ systemctl --user status nextcloudcron.service
        ```

## Updates
* auto-updating the containers requires --label "io.containers.autoupdate=registry" when creating the containers and running systemd service for nextcloud (5. in Setup Procedure) and is then as easy as:
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