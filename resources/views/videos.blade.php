@extends('layouts.main')

@section('content')

<table class="video-table table table-striped table-hover">
    <tbody>
    @foreach ($videos as $video)
    <tr class="video" data-id="{{ $video->videoId }}">
        <td><img src="{{ $video->getThumbnail() }}" alt="" />
        <td><h3>
            @if ($video->isPublished())
            <span class="label label-success pull-right">Published at <span class="time" data-timestamp="{{ $video->getPublishedTimestamp() }}"></span></span>
            @else
            <span class="label label-danger pull-right">Unpublished</span>
            @endif
            <a href="https://www.youtube.com/watch?v={{ $video->videoId }}" target="_blank">{{ $video->getTitle() }}</a>
            </h3>

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

    <div class="form-group">
        <label class="col-sm-2 control-label">At</label>
        <div class="col-sm-10">
            <input class="form-control" type="date" name="execute_at_date" />
            <input class="form-control" type="time" name="execute_at_time" />
            <span class="current-timezone"></span>
            <h5>or</h5>
            <input class="form-control" type="text" name="execute_mins_after_publish" /> minutes after publishing.
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea class="form-control" name="description"></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-black btn-xs"><i class="fa fa-plus-circle"></i> Submit</button>
        </div>
    </div>

</form>

@stop
