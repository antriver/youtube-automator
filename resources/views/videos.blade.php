@extends('layouts.main')

@section('content')

<table class="video-table table table-striped table-hover">
    <tbody>
    @foreach ($videos as $video)
    <tr class="video" data-id="{{ $video->videoId }}">
        <td><img src="{{ $video->getThumbnail() }}" alt="" />
        <td><h3>{!!
            $video->isPublished()
            ? '<span class="label label-success pull-right">Published at ' . date('Y-m-d H:i:s', strtotime($video->getPublishedDate())) . '</span>'
            : '<span class="label label-danger  pull-right">Unpublished</span>'
        !!} <a href="https://www.youtube.com/watch?v={{ $video->videoId }}" target="_blank">{{ $video->getTitle() }}</a></h3>

            <p>{{ Lang::wordTruncate($video->getDescription(), 200) }}</p>

            <hr/>

            <?php
            $descriptionChanges = $video->getDescriptionChanges();
            ?>
            @include('partials.description-changes')

            @if (count($descriptionChanges) > 0)
            <hr/>
            @endif

            <a class="btn btn-black btn-xs btn-add-description-change"><i class="fa fa-plus"></i> Schedule new description</a>

        </td>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>

<form id="add-description-change-form" action="#" class="edit-description-change-form form form-horizontal" style="display:none;">
    {{ csrf_field() }}
    <p>execute_at: <input type="text" name="execute_at" /></p>
    <p>execute_mins_after_publish: <input type="text" name="execute_mins_after_publish" /></p>
    <p>description: <textarea name="description"></textarea></p>
    <button class="btn btn-black btn-sm"><i class="fa fa-plus-circle"></i> Submit</button>
</form>

@stop
