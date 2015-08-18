@extends('layouts.main')

@section('content')

<table class="video-table table table-striped table-hover">
    <tbody>
    @foreach($videos as $video)
    <tr>
        <td><img src="{{ $video->getThumbnail() }}" />
        <td>{{ $video->getTitle() }}</td>
        <td>{!!
            $video->isPublished()
            ? '<span class="label label-success">Published</span>'
            : '<span class="label label-warning">Private</span>'
        !!}</td>
    </tr>
    @endforeach
    </tbody>
</table>

@stop
