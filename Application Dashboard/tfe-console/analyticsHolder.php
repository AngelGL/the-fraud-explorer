<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for Chart
 */

session_start();

if(empty($_SESSION['connected']))
{
 header ("Location: index");
 exit;
}

require 'vendor/autoload.php';
include "inc/open-db-connection.php";
include "inc/agent_methods.php";
include "inc/check_perm.php";
include "inc/elasticsearch.php";

?>

<!-- Styles -->

<style>
	.font-icon-color 
	{ 
	    color: #B4BCC2; 
	}
</style>

<!-- Chart -->

<center>
	<div class="content-graph">
	<div class="graph-insights">
				
	<!-- Leyend -->

	<table class="table-leyend">
		<th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-icon-color">&nbsp;&nbsp;</span>Score leyend</th>
			<tr>
				<td class="table-leyend-point"><span class="point-red"></span><br>31></td>
				<td class="table-leyend-point"><span class="point-green"></span><br>21-30</td>
			</tr>
			<tr>
				<td class="table-leyend-point"><span class="point-blue"></span><br>11-20</td>
				<td class="table-leyend-point"><span class="point-yellow"></span><br>0-10</td>
			</tr>
	</table>
	<br>
	<table class="table-leyend">
        	<th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-icon-color">&nbsp;&nbsp;</span>Opportunity</th>
                	<tr>
                                <td class="table-leyend-point"><span class="point-opportunity-0-10"></span><br>0-10</td>
                                <td class="table-leyend-point"><span class="point-opportunity-11-30"></span><br>11-30</td>
                        </tr>
                        <tr>
                                <td class="table-leyend-point"><span class="point-opportunity-31-60"></span><br>31-60</td>
                                <td class="table-leyend-point"><span class="point-opportunity-61-100"></span><br>61-100</td>
                        </tr>
			<tr>
                                <td class="table-leyend-point"><span class="point-opportunity-101-500"></span><br>101-500</td>
                                <td class="table-leyend-point"><span class="point-opportunity-501-1000"></span><br>501-1000</td>
                        </tr>
	</table>
	<br>

	<!-- Insights -->

	<?php

		$client = Elasticsearch\ClientBuilder::create()->build();
                $configFile = parse_ini_file("config.ini");
                $ESindex = $configFile['es_words_index'];
                $ESalerterIndex = $configFile['es_alerter_index'];
                $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

                $matchesRationalizationCount = countAllFraudTriangleMatches($fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                $matchesOpportunityCount = countAllFraudTriangleMatches($fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                $matchesPressureCount = countAllFraudTriangleMatches($fraudTriangleTerms['p'], $configFile['es_alerter_index']);

                $countRationalizationTotal = $matchesRationalizationCount['count'];
                $countOpportunityTotal = $matchesOpportunityCount['count'];
                $countPressureTotal = $matchesPressureCount['count'];

		echo '<table class="table-insights">';
                echo '<th colspan=2 class="table-insights-header"><span class="fa fa-align-justify font-icon-color">&nbsp;&nbsp;</span>Phrase counts</th>';
                echo '<tr>';
                echo '<td class="table-insights-triangle">Pressure</td>';
                echo '<td class="table-insights-score">'.$countPressureTotal.'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td class="table-insights-triangle">Opportunity</td>';
                echo '<td class="table-insights-score">'.$countOpportunityTotal.'</td>';
                echo '</tr>';
		echo '<tr>';
                echo '<td class="table-insights-triangle">Rationalization</td>';
                echo '<td class="table-insights-score">'.$countRationalizationTotal.'</td>';
                echo '</tr>';
                echo '</table>';
		echo '<br>';

	?>

	<!-- Dictionary -->

	<?php
		$fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');
		$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
		$dictionaryCount = array('pressure'=>'1', 'opportunity'=>'1', 'rationalization'=>'1');

		foreach($fraudTriangleTerms as $term)
		{
			foreach ($jsonFT['dictionary'][$term] as $field => $termPhrase)
			{
				$dictionaryCount[$term]++;		
			}
		}

                echo '<table class="table-dictionary">';
                echo '<th colspan=2 class="table-dictionary-header"><span class="fa fa-align-justify font-icon-color">&nbsp;&nbsp;</span>Dictionary DB</th>';
                echo ' <tr>';
                echo '<td class="table-dictionary-triangle">Pressure</td>';
                echo '<td class="table-dictionary-score">'.$dictionaryCount['pressure'].'</td>';
                echo ' </tr>';
                echo ' <tr>';
                echo '<td class="table-dictionary-triangle">Opportunity</td>';
                echo '<td class="table-dictionary-score">'.$dictionaryCount['opportunity'].'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td class="table-dictionary-triangle">Rationalization</td>';
                echo '<td class="table-dictionary-score">'.$dictionaryCount['rationalization'].'</td>';
                echo '</tr>';
                echo '</table>';
                echo '<br>';
		echo '</div>';
	?>

	<div class="y-axis-line"></div>
	<div class="y-axis-leyend"><span class="fa fa-bar-chart font-icon-color">&nbsp;&nbsp;</span>Incentive, Pressure to commit Fraud</div>

	<div class="x-axis-line-leyend">
        	<br><span class="fa fa-line-chart font-icon-color">&nbsp;&nbsp;</span>Unethical behavior, Rationalization
	</div>

        <div id="scatterplot">

		<?php

			function paintScatter($counter, $opportunityPoint, $agent, $score, $countPressure, $countOpportunity, $countRationalization)
			{
				echo '<span id="point'.$counter.'" class="'.$opportunityPoint.' tooltip-custom" title="<div class=tooltip-inside><b>'.$agent.'</b><table class=tooltip-table><body><tr><td>Total Fraud Score</td><td>'.$score.'</td></tr><tr>
				<td>Pressure count</td><td>'.$countPressure.'</td></tr><tr><td>Opportunity count</td><td>'.$countOpportunity.'</td></tr><tr><td>Rationalization count</td><td>'.$countRationalization.'</td></tr></table>"</div></span>'."\n";
			}

			/* Elasticsearch querys for fraud triangle counts and score */

			$fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

			/* Database querys */

			$result_a = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");

			/* Logic */

			$counter = 1;

			if ($row_a = mysql_fetch_array($result_a))
			{
				do
				{
					$matchesRationalization = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                			$matchesOpportunity = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                			$matchesPressure = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);

                       			$countRationalization = $matchesRationalization['count'];
                       			$countOpportunity = $matchesOpportunity['count'];
                       			$countPressure = $matchesPressure['count'];
				
					$score= ($countPressure+$countOpportunity+$countRationalization)/3;		
					$score = round($score, 1);	

					unset($GLOBALS['numberOfRMatches']);
                                        unset($GLOBALS['numberOfOMatches']);
                                        unset($GLOBALS['numberOfPMatches']);
                                        unset($GLOBALS['numberOfCMatches']);
	
					if ($countOpportunity >= 0 && $countOpportunity <= 10)
					{
                                                if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-0-10-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-0-10-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-0-10-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= 31.0) paintScatter($counter, "point-opportunity-0-10-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
					}
											
					if ($countOpportunity >= 11 && $countOpportunity <= 30)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-11-30-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-11-30-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-11-30-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-11-30-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }
			
					if ($countOpportunity >= 31 && $countOpportunity <= 60)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-31-60-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-31-60-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-31-60-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-31-60-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= 61 && $countOpportunity <= 100)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-61-100-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-61-100-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-61-100-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-61-100-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= 101 && $countOpportunity <= 500)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-101-500-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-101-500-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-101-500-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-101-500-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= 501 && $countOpportunity <= 1000)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-501-1000-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-501-1000-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-501-1000-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-501-1000-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					$counter++;
				}
				while ($row_a = mysql_fetch_array($result_a));
			}

		?>
	</div>
	</div>
	</div>
</center>

<!-- Scatterplot -->

<script type="text/javascript">
$(document).ready(function () {
        $('#scatterplot').scatter({
                color: '#ededed', 
	<?php

        /* Database querys */

        $result_a = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");
	$result_b = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");

        /* Logic */

        $counter = 1;
	
	if ($row_a = mysql_fetch_array($result_a))
        {
        	do
               	{
			$matchesRationalization = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                        $matchesOpportunity = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                        $matchesPressure = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);

                        $countRationalization = $matchesRationalization['count'];
                        $countOpportunity = $matchesOpportunity['count'];
                        $countPressure = $matchesPressure['count'];

			/*  Draw axis units */

			if ($counter == 1)
			{
				$subCounter = 1;

				/* Get max count value for both axis */
			
				if ($row_aT = mysql_fetch_array($result_b))
        			{
                			do
                			{
                        			$matchesRationalizationT = countFraudTriangleMatches($row_aT["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                        			$matchesPressureT = countFraudTriangleMatches($row_aT["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);

                                		$countRationalizationT[$subCounter] = $matchesRationalizationT['count'];
                                		$countPressureT[$subCounter] = $matchesPressureT['count'];
	
						$subCounter++;
					}
                			while ($row_aT = mysql_fetch_array($result_b));
				}

				$GLOBALS['maxXAxis'] = max($countPressureT);
				$GLOBALS['maxYAxis'] = max($countRationalizationT);

				echo 'rows: '.$maxYAxis.','; 
                		echo 'columns: 0,'."\n"; 
                		echo 'subsections: '.$maxXAxis.','; 
                		echo 'responsive: true';
        			echo '});';
     			}

			/* Scoring calculation */

			$score=($countPressure+$countOpportunity+$countRationalization)/3;
                        $xAxis = ($countPressure*100)/$GLOBALS['maxXAxis'];
                        $yAxis = ($countRationalization*100)/$GLOBALS['maxYAxis'];

			/* Fix corners */

   			if ($xAxis == 100) $xAxis = $xAxis - 2;
			if ($yAxis == 100) $yAxis = $yAxis - 5;
			if ($xAxis == 0) $xAxis = $xAxis + 2;
                        if ($yAxis == 0) $yAxis = $yAxis + 3;			

                        if ($countOpportunity >= 0 && $countOpportunity <= 10)
                        {
       		                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 11 && $countOpportunity <= 30)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 31 && $countOpportunity <= 60)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }
		
			if ($countOpportunity >= 61 && $countOpportunity <= 100)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 101 && $countOpportunity <= 500)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 501 && $countOpportunity <= 1000)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        $counter++;
		}
		while ($row_a = mysql_fetch_array($result_a));
	}
	?>
});
</script>

<!-- Tooltipster -->

<script>
	$(document).ready(function()
	{
        	$('.tooltip-custom').tooltipster(
       	 	{
               	 	theme: 'tooltipster-light',
                	contentAsHTML: true
        	});
	});
</script>
