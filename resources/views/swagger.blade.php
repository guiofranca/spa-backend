<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="SwaggerUI" />
    <title>SwaggerUI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui.css" />
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" crossorigin></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-standalone-preset.js" crossorigin></script>
    <script>
        window.onload = () => {
            var config = {
                urls: [{
                        url: '{{ asset('openapi_v1.json') }}',
                        name: "v1"
                    },
                    {
                        url: '{{ asset('openapi_v2.json') }}',
                        name: "v2"
                    },
                ],
                dom_id: '#swagger-ui'
            };

            var auth = {
                "scopeSeparator": " ",
                "scopes": [],
                "useBasicAuthenticationWithAccessCodeGrant": false,
                "usePkceWithAuthorizationCodeGrant": false
            };

            config.dom_id = "#swagger-ui";
            config.presets = [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset];
            config.layout = "StandaloneLayout";

            const ui = SwaggerUIBundle(config);
            ui.initOAuth(auth);
            window.ui = ui;
        };
    </script>
</body>

</html>
