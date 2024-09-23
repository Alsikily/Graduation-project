<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
    </head>
    <body>
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script>

            Pusher.logToConsole = true;

            var pusher = new Pusher('b71b1ae0a0d8bcacaf26', {
                cluster: 'eu',
                authEndpoint: `/broadcasting/auth`,
                // auth: {
                //     Authorization: {

                //     },
                //     headers: {
                //         "X-CSRF-Token": "{{ csrf_token() }}"
                //     }
                // }
                headers: { "X-CSRF-Token": "csrf_token()" },
            });

            var channel = pusher.subscribe('private-channel');
            channel.bind('my-event', function(data) {
                alert(JSON.stringify(data));
            });

        </script>
    </body>
</html>
