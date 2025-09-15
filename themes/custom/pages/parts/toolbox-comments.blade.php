{{--
$comments - CommentTree
--}}
<div refs="editor-toolbox@tab-content" data-tab-content="comments" class="toolbox-tab-content">
    <h4>{{ trans('entities.comments') }}</h4>

    <div class="comment-container-compact px-l">
        <p class="text-muted small mb-m">
            {{ trans('entities.comment_editor_explain') }}
        </p>
        {{-- Comments functionality temporarily disabled due to API compatibility issues --}}
        <p class="italic text-muted">{{ trans('entities.comment_none') }}</p>
    </div>
</div>