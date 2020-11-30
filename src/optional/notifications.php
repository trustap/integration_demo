<?php
    require '../init.php';
?>
<html>
    <head>
        <style>
#notifs_count {
    width: 3.0rem;
    height: 3.0rem;
    background-color: #efefef;
}
        </style>
    </head>
    <body>
        <h1>Notifications</h1>
        <div id="notifs_count"></div>
        <div id="notifications">&nbsp;</div>
        <script src="<?php echo $trustap_lib; ?>"></script>
        <script src="/js/trustapi_config.js"></script>
        <script>
            const trustApi = trustap.api(trustApiConf);

            trustApi.notifications.setCounterWidget('notifs_count');

            trustApi.notifications.listen(function (loggedIn, notifs, err) {
                if (err) {
                    console.error(err);
                    return;
                }

                if (!loggedIn) {
                    const ns = document.getElementById('notifications');
                    ns.innerHTML = '';

                    const btn = document.createElement('button');
                    btn.innerHTML = 'Log In';
                    btn.addEventListener('click', function () {
                        trustApi.logIn().then(trustApi.notifications.check);
                    });

                    const span = document.createElement('span');
                    span.innerHTML = ' to see your Trustap notifications';

                    ns.appendChild(btn);
                    ns.appendChild(span);
                    return;
                }

                if (notifs.length === 0) {
                    document.getElementById('notifications').innerHTML =
                        'No notifications';
                } else {
                    const ns = document.getElementById('notifications');
                    ns.innerHTML = 'You have new notifications:';

                    const ul = document.createElement('ul');
                    notifs.forEach((notif) => {
                        const li = document.createElement('li');
                        const target = notif.target.split('/');
                        const route = target[0] === 'basic' ? 'online' : 'f2f_widget';
                        li.innerHTML =
                            notif.description +
                            ' <a href="/'+route+'/transactions.php?transaction_id='+target[1]+'">Open</a> ';
                        const clear = document.createElement('a');
                        clear.href = '#';
                        clear.innerHTML = 'Mark as Read';
                        clear.addEventListener('click', function () {
                            trustApi.notifications.markAsRead(notif.id).then(
                                trustApi.notifications.check
                            );
                        });
                        li.appendChild(clear);
                        ul.appendChild(li);
                    });
                    ns.appendChild(ul);
                }
            });
        </script>
    </body>
</html>
