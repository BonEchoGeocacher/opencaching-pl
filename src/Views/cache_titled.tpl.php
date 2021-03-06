<?php
use src\Utils\I18n\I18n;
?>

<link rel="stylesheet" type="text/css" media="screen,projection" href="/css/GCT.css" />
<link rel="stylesheet" type="text/css" media="screen,projection" href="/css/GCTStats.css" />
<script src='https://www.google.com/jsapi'></script>
<script src="/js/GCT.js"></script>
<script src="/js/GCT.lang.php"></script>
<script src="/js/GCTStats.js"></script>
<script src="/js/wz_tooltip.js"></script>


<div class="content2-pagetitle"><img src="/images/blue/TitledCache.png" class="icon32" alt="" title="" align="middle" />&nbsp;<?php global $titled_cache_period_prefix; $ntitled_cache = $titled_cache_period_prefix.'_titled_caches'; echo tr($ntitled_cache); ?></div>

<br>
<br>

<div id='idGTC' align = "center"> </div>


<script>;
<?php echo "GCTLoad( 'ChartTable', '" . I18n::getCurrentLang() . "' );";?>
</script>


<script>
    var gct = new GCT('idGTC');
    gct.addColumn('string', '' ); //
    gct.addColumn('string',  '<?php echo tr("geocache") ?>', 'font-size: 12px; ' ); //1
    gct.addColumn('string', '<?php echo tr("region") ?>', 'font-size: 12px; ' ); //2
    gct.addColumn('string', '<?php echo tr("owner") ?>', 'font-size: 12px; ' ); //3
    gct.addColumn('string', '<?php echo tr("titled_cache_date") ?>', 'font-size: 12px; ' ); //4

    gct.addChartOption('showRowNumber', true );

    gct.addChartOption('sortColumn', 4 ); //Date
    gct.addChartOption('sortAscending', false );
    gct.addChartOption('pageSize', 20);

    gct.addVisualOptionVC('rowNumberCell', 'GCT-color-none GCT-font-size11 ');
</script>

<script>
{contentTable}
gct.drawChart();
</script>
