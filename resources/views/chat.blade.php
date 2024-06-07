<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Pusher</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style type="text/css" media="screen">
        #messages {
            color: #1abc9c;
        }

        #messages li {
            max-width: 50%;
            margin-bottom: 10px;
            border-color: #34495e;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <h1>Laravel & Pusher: Demo real-time web application.</h1>

            <p>Message preview:</p>
            <ul id="messages" class="list-group"></ul>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        // Pusher.logToConsole = true;

        var pusher = new Pusher('eac5c6dfd97847e00aaf', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('chat');
        channel.bind('chat-realtime', function(data) {
            // console.log(JSON.stringify(data));
            let message = document.getElementById('messages');
            let newListItem = document.createElement('li');

            newListItem.textContent = data.message;
            message.appendChild(newListItem);
        });
    </script>
</body>

</html>
