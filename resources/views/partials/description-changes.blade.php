<ul class="description-changes">
@foreach ($descriptionChanges as $change)

    <li
        data-id="{{ $change->id }}"
        data-execute-at="{{ $change->execute_at ? date('r', strtotime($change->execute_at)) : '' }}"
        data-execute-mins-after-publish="{{ $change->execute_mins_after_publish }}">

        <a href="#" class="btn-delete-description-change btn btn-black btn-xs"><i class="fa fa-trash-o"></i></a>
        <a href="#" class="btn-edit-description-change btn btn-black btn-xs"><i class="fa fa-pencil"></i></a>

        @if ($change->executed_at)
        <span class="label label-sm label-default changed"><i class="fa fa-check-circle"></i> Changed at <span class="time" data-timestamp="{{ strtotime($change->executed_at) }}"></span></span>
        @elseif ($change->execute_at)
        <span class="label label-sm label-info"><i class="fa fa-clock-o"></i> Scheduled for <span class="time" data-timestamp="{{ strtotime($change->execute_at) }}"></span></span>
        @elseif ($change->execute_mins_after_publish)
        <span class="label label-sm label-info"><i class="fa fa-clock-o"></i> Scheduled for {{ $change->execute_mins_after_publish / 60 }} hours after publishing</span>
        @endif

        {{ Lang::wordTruncate($change->description, 200) }}

        <span style="display:none;" class="full-description">{{ $change->description }}</span>
     </li>

@endforeach
</ul>
