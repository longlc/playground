
<script type="text/javascript">
	var mapDiv,
		map,
		infobox,
		mapCenter,
		mapZoom;
	jQuery(document).ready(function($) {
			mapDiv = $("#directory-main-bar");
			map = mapDiv.height({!$themeOptions->directoryMap->mapHeight}).gmap3({
				map: {
					options: {
						{foreach parseMapOptions($themeOptions->directoryMap) as $key => $value}
						{if $iterator->first}{$key}: {!$value}{else},{$key}: {!$value}{/if}
						{/foreach}
						{if (count($items) >= 1) && (!isset($isGeolocation))}
						,center: [{if $items[0]->optionsDir['gpsLatitude']}{!$items[0]->optionsDir['gpsLatitude']}{else}0{/if},{if $items[0]->optionsDir['gpsLongitude']}{!$items[0]->optionsDir['gpsLongitude']}{else}0{/if}]
						,zoom: {!$themeOptions->directory->setZoomIfOne}
						{/if}
					}
				}
				{if $isGeolocation && (!isset($_GET['geo-lat'])) && (!isset($_GET['geo-lng']))}
				,getgeoloc:{
					callback : function(latLng){

						if (latLng){
							$(this).gmap3({
								map:{
									options:{
										center: latLng,
										zoom: 13
									}
								}
							});
							$('#dir-searchinput-geo-lat').val(latLng.lat());
							$('#dir-searchinput-geo-lng').val(latLng.lng());
							ajaxGetMarkers(false,latLng);
						}

					}
				}
				{else}
				,marker: {
					values: [
						{foreach $items as $item}
							{
								latLng: [{if $item->optionsDir['gpsLatitude']}{!$item->optionsDir['gpsLatitude']}{else}0{/if},{if $item->optionsDir['gpsLongitude']}{!$item->optionsDir['gpsLongitude']}{else}0{/if}],
								options: {
									icon: "{!$item->marker}",
									shadow: "{!$themeOptions->directoryMap->mapMarkerImageShadow}",
								},
								data: '<div class="marker-holder"><div class="marker-content{ifset $item->thumbnailDir} with-image"><img src="{timthumb src => $item->thumbnailDir, w => 120, h => 160}" alt="">{else}">{/ifset}<div class="map-item-info"><div class="title">'+{ifset $item->post_title}{$item->post_title}+{/ifset}'</div>{if $item->rating}<div class="rating">{for $i=1; $i <= $item->rating["max"]; $i++}<div class="star{if $i <= $item->rating["val"]} active{/if}"></div>{/for}</div>{/if}<div class="address">'+{ifset $item->optionsDir["address"]}{$item->optionsDir["address"]|nl2br}+{/ifset}'</div><a href="{!$item->link}" class="more-button">' + {__ 'VIEW MORE'} + '</a></div><div class="arrow"></div><div class="close"></div></div></div></div>'
							}
						{if !($iterator->last)},{/if}
						{/foreach}
					],
					options:{
						draggable: false
					},
					cluster:{
	              		radius: 20,
						// This style will be used for clusters with more than 0 markers
						0: {
							content: "<div class='cluster cluster-1'>CLUSTER_COUNT</div>",
							width: 90,
							height: 80
						},
						// This style will be used for clusters with more than 20 markers
						20: {
							content: "<div class='cluster cluster-2'>CLUSTER_COUNT</div>",
							width: 90,
							height: 80
						},
						// This style will be used for clusters with more than 50 markers
						50: {
							content: "<div class='cluster cluster-3'>CLUSTER_COUNT</div>",
							width: 90,
							height: 80
						},
						events: {
							click: function(cluster) {
								map.panTo(cluster.main.getPosition());
								map.setZoom(map.getZoom() + 2);
							}
						}
	              	},
					events: {
						click: function(marker, event, context){
							map.panTo(marker.getPosition());

							infobox.setContent(context.data);
							infobox.open(map,marker);

							// if map is small
							var iWidth = 260;
							var iHeight = 300;
							if((mapDiv.width() / 2) < iWidth ){
								var offsetX = iWidth - (mapDiv.width() / 2);
								map.panBy(offsetX,0);
							}
							if((mapDiv.height() / 2) < iHeight ){
								var offsetY = -(iHeight - (mapDiv.height() / 2));
								map.panBy(0,offsetY);
							}

						}
					}
				}
				{/if} {* end ifset geolocation *}
			}{if (count($items) > 1) && (!isset($isGeolocation))},"autofit"{/if});

			map = mapDiv.gmap3("get");
	        infobox = new InfoBox({
	        	pixelOffset: new google.maps.Size(-50, -65),
	        	closeBoxURL: '',
	        	enableEventPropagation: true
	        });
	        mapDiv.delegate('.infoBox .close','click',function () {
	        	infobox.close();
	        });

	        if (Modernizr.touch){
	        	{ifset $themeOptions->directoryMap->draggableForTouch}map.setOptions({ draggable : true });{else}map.setOptions({ draggable : false });{/ifset}
	        	{ifset $themeOptions->directoryMap->draggableToggleButton}
		        var draggableClass = {ifset $themeOptions->directoryMap->draggableForTouch}'active'{else}'inactive'{/ifset};
		        var draggableTitle = {ifset $themeOptions->directoryMap->draggableForTouch}{__ 'Deactivate map'}{else}{__ 'Activate map'}{/ifset};
		        var draggableButton = $('<div class="draggable-toggle-button '+draggableClass+'">'+draggableTitle+'</div>').appendTo(mapDiv);
		        draggableButton.click(function () {
		        	if($(this).hasClass('active')){
		        		$(this).removeClass('active').addClass('inactive').text({__ 'Activate map'});
		        		map.setOptions({ draggable : false });
		        	} else {
		        		$(this).removeClass('inactive').addClass('active').text({__ 'Deactivate map'});
		        		map.setOptions({ draggable : true });
		        	}
		        });
		        {/ifset}
		    }

		    {if $isGeolocation && (isset($_GET['geo-lat'])) && (isset($_GET['geo-lng'])) && (isset($_GET['geo-radius']))}
		    generateOnlyGeo({$_GET['geo-lat']},{$_GET['geo-lng']},{$_GET['geo-radius']});
		    {/if}
		
		mapCenter = map.getCenter();
		mapZoom = map.getZoom();

	    {include 'ajaxfunctions-javascript.php'}

	});
</script>
