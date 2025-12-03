<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> {Lang::T('Activity Log')}</h3>
        <div class="box-tools pull-right">
            <a href="{Text::url('logs')}" class="btn btn-box-tool" title="{Lang::T('View All')}">
                <i class="fa fa-external-link"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        <ul class="timeline timeline-simple">
            {foreach $dlog as $dl}
                <li class="timeline-item">
                    <span class="timeline-dot"></span>
                    <div class="timeline-content">
                        <small class="text-muted">{Lang::timeElapsed($dl['date'],true)}</small>
                        <p>{$dl['description']}</p>
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
</div>