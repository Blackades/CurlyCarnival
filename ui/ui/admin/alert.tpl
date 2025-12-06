<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{ucwords(Lang::T($type))} - {$_c['CompanyName']}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/modern-AdminLTE.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill-modern.css">
    <meta http-equiv="refresh" content="{$time}; url={$url}">
</head>

<body class="alert-page lockscreen-page">
    <div class="alert-page-wrapper lockscreen-wrapper">
        <!-- Alert Card Container -->
        <div class="alert-card card panel panel-{$type} shadow-lg">
            <!-- Alert Header -->
            <div class="alert-header card-header panel-heading">
                <div class="alert-title-wrapper">
                    <h1 class="alert-title card-title">
                        <i class="alert-icon fa {if $type == 'success'}fa-check-circle{elseif $type == 'warning'}fa-exclamation-triangle{elseif $type == 'danger'}fa-times-circle{else}fa-info-circle{/if}" aria-hidden="true"></i>
                        <span class="title-text">{ucwords(Lang::T($type))}</span>
                    </h1>
                </div>
            </div>
            
            <!-- Alert Body -->
            <div class="alert-body card-body panel-body">
                <div class="alert-content">
                    <div class="alert-message">
                        {$text}
                    </div>
                </div>
            </div>
            
            <!-- Alert Footer -->
            <div class="alert-footer card-footer panel-footer">
                <div class="alert-actions">
                    <a href="{$url}" id="button" class="btn btn-{$type} btn-lg btn-block alert-action-btn" 
                       role="button" aria-label="Continue to next page">
                        <i class="btn-icon fa fa-arrow-right" aria-hidden="true"></i>
                        <span class="btn-text">{Lang::T('Click Here')} (<span id="countdown">{$time}</span>)</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="alert-page-footer lockscreen-footer text-center">
            <div class="company-info">
                <span class="company-name">{$_c['CompanyName']}</span>
            </div>
        </div>
    </div>

    <script>
        // Enhanced countdown timer with better accessibility
        var time = {$time};
        var countdownElement = document.getElementById("countdown");
        var buttonElement = document.getElementById("button");
        
        timer();

        function timer() {
            setTimeout(() => {
                time--;
                if (time > -1) {
                    // Update countdown display
                    if (countdownElement) {
                        countdownElement.textContent = time;
                    }
                    
                    // Update button text with icon
                    if (buttonElement) {
                        buttonElement.innerHTML = '<i class="btn-icon fa fa-arrow-right" aria-hidden="true"></i>' +
                                                '<span class="btn-text">{Lang::T('Click Here')} (' + time + ')</span>';
                    }
                    
                    timer();
                } else {
                    // Auto-redirect when timer reaches 0
                    window.location.href = "{$url}";
                }
            }, 1000);
        }
        
        // Add keyboard support for better accessibility
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                window.location.href = "{$url}";
            }
        });
    </script>
</body>

</html>