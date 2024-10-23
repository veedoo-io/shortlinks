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
    <div class="container center">
        <form action="{{ route('gpt.create') }}" method="POST">
            @csrf
            <div class="mb-2">
                <label for="textCreate" class="form-label">api Key</label>
                <input type="text" name="apiKey" class="form-control" placeholder="api Key" autofocus>
            </div>
            <div class="mb-2">
                <label for="textAudio" class="form-label">text Audio</label>
                <input type="text" name="textAudio" class="form-control" placeholder="text Audio">
            </div>
            <div class="mb-2">
                <label for="voice" class="form-label">Voice options</label>
                <select type="text" name="voice" class="form-control">
                    <option name="alloy" selected="selected">alloy</option>
                    <option name="echo">echo</option>
                    <option name="fable">fable</option>
                    <option name="onyx">onyx</option>
                    <option name="nova">nova</option>
                    <option name="shimmer">shimmer</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <div>
            @php $audioList = \Illuminate\Support\Facades\Storage::disk('public')->files('audio'); sort($audioList); @endphp
            @foreach($audioList as $audio)
                <figcaption>{{ $audio }}</figcaption>
                <audio controls src="{{ Storage::disk('public')->url($audio) }}"></audio>
            @endforeach

        </div>
    </div>
</body>
</html>
