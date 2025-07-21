<!DOCTYPE html>

<html lang="en-us">
@include('components.partials.head')


<body>
    @include('components.partials.nav')

    {{ $slot }}
</body>

@include('components.partials.script')

</html>
