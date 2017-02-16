<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BitCoin Ticker - Simple Demo</title>
</head>
<body>
    @if (isset($output) && count($output))
        <table style="width: 80%;" align="center" cellpadding="5" cellspacing="5" border="1">
            <tr>
                <th>&nbsp;</th>

                @foreach ($output as $currency => $rate)
                    <th>{{ $currency }}</th>
                @endforeach
            </tr>
            <tr>
                <td>Rate: </td>
                @foreach ($output as $currency => $rate)
                    <td align="center" id="{{ sha1('rate_'.$currency) }}">{{ $rate['rate'] }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Sources: </td>
                @foreach ($output as $currency => $rate)
                    <td align="center" id="{{ sha1('source_'.$currency) }}">{{ $rate['active_sources'] }}</td>
                @endforeach
            </tr>
        </table>
    @endif

    <script type="text/javascript">
        var xhr_request_timeout;

        (function xhr() {
            if (xhr_request_timeout)
                clearInterval(xhr_request_timeout);

            var xhttp = new XMLHttpRequest();

            xhttp.onreadystatechange = function () {
                if (this.readyState == 4) {
                    if (this.status == 200) {
                        var json = JSON.parse(this.responseText),
                            key;

                        for (key in json) {
                            if (json.hasOwnProperty(key)) {
                                if (json[key].hasOwnProperty('rate_object') && json[key].hasOwnProperty('rate'))
                                    document.getElementById(json[key]['rate_object']).innerHTML = json[key]['rate'];

                                if (json[key].hasOwnProperty('source_object') && json[key].hasOwnProperty('source'))
                                    document.getElementById(json[key]['source_object']).innerHTML = json[key]['source'];
                            }
                        }
                    }

                    xhr_request_timeout = setInterval(function() {
                        xhr();
                    }, 1000);
                }
            };

            xhttp.open("GET", "/", true);
            xhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhttp.send();
        })();
    </script>
</body>
</html>
