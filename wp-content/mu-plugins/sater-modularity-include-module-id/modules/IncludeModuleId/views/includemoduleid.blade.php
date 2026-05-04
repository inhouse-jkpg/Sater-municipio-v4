@if (!empty($includedMarkup))
    {!! $includedMarkup !!}
@elseif (is_admin())
    <div style="padding:10px;border:1px solid #ccd0d4;background:#fff;">
        <strong>Include module (ID):</strong>
        <span>
            Nothing rendered. Check that “Module ID” is a valid Modularity module (post type <code>mod-*</code>)
            and that this module is not hidden (eye icon) in the Modularity editor.
        </span>
    </div>
@endif

