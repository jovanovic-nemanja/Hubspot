<html>

<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }}</title>
  <script>
    window.opener.postMessage({
      access_token: "{{ $token }}",
      refresh_token: "{{ $refreshToken }}",
      provider: "{{ $provider }}",
      provider_user_id: "{{ $user['user_id'] }}",
      hub_id: "{{ $user['hub_id'] }}",
      hub_domain: "{{ $user['hub_domain'] }}"
    }, "{{ url('/') }}")
    window.close()
  </script>
</head>

<body>
</body>

</html>