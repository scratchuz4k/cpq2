<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    @vite('resources/scss/error.scss')
</head>

<body class="font-sans antialiased">
    <div class="container error-body">
        <div class="row h-100">
            <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                <div class="flex items-center text-center">
                    <span class="text-primary display-1">@yield('code')</span>
                </div>
                <div class="flex items-center mb-3 text-center">
                    <span class="text-primary fs-3 font-weight-bold">@yield('title')</span>
                </div>
                <div class="flex items-center text-center">
                    <span class="text-white fs-5">@yield('message')</span>
                </div>
                <br />
                <br />
                <div class="flex items-center justify-center">
                    <div>
                        <a class="btn btn-primary text-uppercase px-4" href="/">Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
