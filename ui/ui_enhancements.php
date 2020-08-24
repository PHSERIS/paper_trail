<?php

namespace Partners\autoPdfUpload;

$script = <<<SCRIPT
<script type="text/javascript"> 
                    $(document).ready(function() {
                        $("#sub-nav ul li").last().after("<li><a href=\"{$_SERVER['PHP_SELF']}?pid={$project_id}&type=pdf_archive\" style=\"font-size:14px;color:#393733\"><i class=\"fas fa-file-pdf fs14\"></i> PDF Survey Archive</a></li>");
                        });
            </script>
SCRIPT;
print $script;


?>