{if $paginator}
    <div class="pagination-wrapper text-center">
        <nav class="pagination-nav" aria-label="Page navigation" role="navigation">
            <ul class="pagination pagination-modern">
                <!-- Previous Page Link -->
                <li class="page-item pagination-item prev-item {if empty($paginator['prev'])}disabled{/if}">
                    <a class="page-link pagination-link prev-link" 
                       href="{if !empty($paginator['prev'])}{$paginator['url']}{$paginator['prev']}{else}#{/if}" 
                       aria-label="Go to previous page"
                       {if empty($paginator['prev'])}tabindex="-1" aria-disabled="true"{/if}>
                        <i class="pagination-icon fa fa-chevron-left" aria-hidden="true"></i>
                        <span class="pagination-text">{Lang::T('Prev')}</span>
                    </a>
                </li>
                
                <!-- Page Number Links -->
                {foreach $paginator['pages'] as $page}
                    <li class="page-item pagination-item number-item {if $paginator['page'] == $page}active current{elseif $page == '...'}disabled ellipsis{/if}">
                        {if $page == '...'}
                            <span class="page-link pagination-link ellipsis-link" aria-label="More pages">
                                <span class="pagination-ellipsis">{$page}</span>
                            </span>
                        {elseif $paginator['page'] == $page}
                            <span class="page-link pagination-link current-link" aria-current="page" aria-label="Current page, page {$page}">
                                <span class="pagination-number">{$page}</span>
                            </span>
                        {else}
                            <a class="page-link pagination-link number-link" 
                               href="{$paginator['url']}{$page}" 
                               aria-label="Go to page {$page}">
                                <span class="pagination-number">{$page}</span>
                            </a>
                        {/if}
                    </li>
                {/foreach}
                
                <!-- Next Page Link -->
                <li class="page-item pagination-item next-item {if $paginator['page']>=$paginator['count']}disabled{/if}">
                    <a class="page-link pagination-link next-link" 
                       href="{if $paginator['page']<$paginator['count']}{$paginator['url']}{$paginator['next']}{else}#{/if}" 
                       aria-label="Go to next page"
                       {if $paginator['page']>=$paginator['count']}tabindex="-1" aria-disabled="true"{/if}>
                        <span class="pagination-text">{Lang::T('Next')}</span>
                        <i class="pagination-icon fa fa-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Pagination Info -->
        <div class="pagination-info">
            <small class="pagination-summary text-muted">
                <span class="pagination-info-text">
                    {if isset($paginator['total']) && $paginator['total'] > 0}
                        {Lang::T('Showing')} 
                        <span class="pagination-range-start">{($paginator['page'] - 1) * $paginator['per_page'] + 1}</span>
                        {Lang::T('to')} 
                        <span class="pagination-range-end">{min($paginator['page'] * $paginator['per_page'], $paginator['total'])}</span>
                        {Lang::T('of')} 
                        <span class="pagination-total">{$paginator['total']}</span>
                        {Lang::T('entries')}
                    {else}
                        {Lang::T('Page')} <span class="pagination-current">{$paginator['page']}</span> {Lang::T('of')} <span class="pagination-total-pages">{$paginator['count']}</span>
                    {/if}
                </span>
            </small>
        </div>
    </div>
{/if}