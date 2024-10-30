<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>{{ env('APP_NAME', 'Link-cut') }}</title>

    <!-- Styles -->
    <style>
        .center {
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 50%;
        }

        .min-vh-100 {
            height: 100vh;
        }

        .align-content-center {
            align-content: center;
        }

        .img {
            width: 256px;
            height: 158px;
        }
    </style>
</head>
<body class="min-vh-100 align-content-center">

<div class="container center mt-5">
    <form action="{{ route('beyond.downloadAudioByProject') }}" method="POST">
        @csrf
{{--        <div class="mb-2">--}}
{{--            <label for="projectId" class="form-label">projectId</label>--}}
{{--            <input type="number" name="projectId" class="form-control" placeholder="text Audio" required/>--}}
{{--        </div>--}}
        <div class="mb-2">
            <label for="projectId" class="form-label">projectId</label>
            <select type="text" name="projectId" class="form-control">
                @foreach($lists as $project)
                    <option value="{{ $project['id'] }}">{{ $project['id'] }}-{{ $project['name'] }}-{{ $project['language'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary mb-4">Submit</button>
    </form>

</div>
<script>
</script>
</body>
</html>
