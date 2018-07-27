<form action="/phone" method="post">
    {{ csrf_field() }}

    <div>
        <label for="number">Phone number:</label>
        <input placeholder="27831234567" type="text" name="number" id="number" value="{{ session('number') }}">
    </div>

    <button>Submit phone number</button>

    @if (count($errors) > 0)
        <div class="alert">
            <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <strong>{{ $error }}</strong>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert">
            <br><br>
            <ul>
                number: <strong>{{ session('number') }}</strong>
                status: <strong>{{ session('status') }}</strong>
                @if (session('motive'))
                    motive: <strong>{{ session('motive') }}</strong>
                @endif
            </ul>
        </div>
    @endif

</form>
