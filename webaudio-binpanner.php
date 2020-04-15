<!--- MODIFIED MULTICHANNEL AUDIO TO BINAURAL AUDIO PLAYER --->
<!--- by Harold Groenenboom                       --->
<!--- Base code: https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API/Web_audio_spatialization_basics --->

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Web Audio Spacialisation</title>
        <meta name="description" content="Panner node demo for Web Audio API">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- https://www.warpdesign.fr/webaudio-from-scriptprocessornode-to-the-new-audioworklet-api/ -->
        <!-- https://github.com/GoogleChromeLabs/audioworklet-polyfill -->
        <!--<script src="https://unpkg.com/audioworklet-polyfill/dist/audioworklet-polyfill.js"></script> <!-- since ScriptProcessorNode is used in most browser, but deprectated! And the new AudioWorklet is favored by WebAudio, but only support by Chrome. -->
        
        <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,700" rel="stylesheet">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <link href="css/customStyle.css" rel="stylesheet">
        <!--<link href="circularslider/circularslider.css" rel="stylesheet">-->
        
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8=" crossorigin="anonymous"></script>
        <?php
            # SET DEFAULT PARAMETERS
            $NUM_AUDIO_FILES = 8;
            parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
            if(isset($array["channels"])) {
                $NUM_AUDIO_FILES = (int)$array["channels"];
            }
            parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
            if(isset($array["debuglevel"])) {
                echo '<script type="text/javascript">var SHOULD_LOG=parseFloat('.$array["debuglevel"].');</script>';
            } else {
                echo '<script type="text/javascript">var SHOULD_LOG=-1;</script>';
            }
        ?>
    </head>

    <body>
        <!-- main div -->
        <div style="background-image: url('/img/test3.gif');background-origin: content-box;background-repeat: repeat-y;background-size: cover;background-position-x: center;position:absolute;width:100%;height:100%;">
            <div id="loading screen">
                <p style="margin:auto;font-size:5vw;text-align:center">loading...</p>
                <p id="loading-text" style="margin:auto;font-size:3.5vw;text-align:center;padding:100px">0/0</p>
            </div>
            
            <div class="customContainer" id="octophonic player" style="display:none;">
                <!-- canvas space -->
                <div class="frameSpace drawFrameSpace">
                    <?php
                        # GENERATE CANVAS
                        parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                        $str = '<canvas class="canvas" id="canvas" width="1000" height="1000"';
                        echo $str.'>canvas</canvas>';
                    ?>
                    <div style="height:100%;display:inline-block;width:3%;float:right;background-color:grey;opacity:0.4" id="drawCanvasButton"></div>
                </div> <!-- canvas space -->
                
                <!-- faders -->
                <div class="frameSpace" style="margin:auto;">
                    <!-- track volume fader -->
                    <div>
                    <?php
                        # DISPLAY FILE LOADED
                        parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                        if(isset($array["file"])) {
                            echo '<p style="margin:0px;font-size:13px;display:none;">'.$array["file"].'</p>';
                        }
                    ?>
                    </div>
                    <div class="sliders">
                        <label for="trackVolume">track volume</label>
                        <input type="range" id="trackVolume" class="control-trackVolume slider" min="0" max="1.5" value="0.8" list="trackVolume-vals" step="0.01" data-action="trackVolume" />
                        <datalist id="trackVolume-vals">
                            <option value="0" label="min"></option>
                            <option value="1.5" label="max"></option>
                        </datalist>
                    </div>
                
                    <!-- reverb volume fader -->
                    <div class="sliders" 
                        <?php
                        parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                        if(!isset($array["debug_level"])) { echo 'style="display:none;"'; }
                        ?>
                    >
                        <label for="reverb">reverb volume</label>
                        <input type="range" id="reverb" class="control-reverb slider" min="0" max="0.5" value="0.36" list="reverb-vals" step="0.01" data-action="reverb" />
                        <datalist id="reverb-vals">
                            <option value="0" label="min"></option>
                            <option value="0.5" label="max"></option>
                        </datalist>
                    </div>

                    <!-- master volume fader -->
                    <div class="sliders" 
                        <?php
                        parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                        if(!isset($array["debug_level"])) { echo 'style="display:none;"'; }
                        ?>
                    >
                        <label for="volume">master</label>
                        <input type="range" id="volume" class="control-volume slider" min="0" max="1.5" value="0.9" list="gain-vals" step="0.01" data-action="volume" />
                        <datalist id="gain-vals">
                            <option value="0" label="min"></option>
                            <option value="1.5" label="max"></option>
                        </datalist>
                    </div>
                    
                    <!-- panning fader -->
                    <div class="sliders">
                        <label for="pan">rotate speakers</label>
                        <input type="range" id="pan" class="control-panning slider circular-slider" min="0" max="6.28" value="3.745" list="pan-vals" step="0.01" data-action="pan" />
                        <datalist id="pan-vals">
                            <option value="0" label="min"></option>
                            <option value="6.28" label="max"></option>
                        </datalist>
                    </div>
                    <div class="sliders">
                        <button data-reverb="false" id="reverbbutton" role="switch" aria-checked="false">
                            <span>Reverb on</span>
                        </button>                    
                    </div>
                    <div class="sliders">
                        <button data-playing="false" id="playbutton" role="switch" aria-checked="false">
                        <span>Play/Pause</span>
                        </button>
                    </div>
                </div> <!-- faders -->

                <!-- audio sources -->
                <section class="tape">
                    <?php
                        #GENERATE AUDIO FILES ELEMENTS
                        parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                        if(isset($array["file"]) && isset($array["ext"])) 
                        {
                            # IF EXTENSION AND FILE IS DEFINED
                            for($i = 1; $i < $NUM_AUDIO_FILES+1; $i++) {
                                echo '<audio ';
                                echo 'src="'.$array["file"].$i.$array["ext"].'" async preload crossorigin="anonymous">';
                                echo '</audio>';
                            }
                        } 
                        else if(isset($array["file"])) 
                        {
                            # IF ONLY FILE IS DEFINED (BUGGY!!!!)
                            for($i = 1; $i < $NUM_AUDIO_FILES+1; $i++) {
                                echo '<audio>';
                                echo '<source="' . str_replace(array('/'), array('%2F'),$array["file"]) . $i . '.mp3" type="audio/mpeg">';
                                // echo '<source="'.$array["file"].$i.'.ogg" type="audio/ogg">';
                                // echo '<source="'.$array["file"].i.'.m4a">'
                                // echo '<source="'.$array["file"].i.'.wav">'
                                echo '</audio>';
                            }
                        }
                        else 
                        {
                            echo '<h>no valid audiofile selected! please enter a valid audiofile like this: www.haroldgroenenboom.nl/other/webaudio-binpanner/webaudio-binpanner.php?file<enter your file url here!></h>';
                        }
                    ?>
                </section> <!-- audio sources -->
                
            </div> <!-- octophonic player -->
            <?php
                # GENERATE DEBUG INTERFACE (PANNER CONTROLS)
                parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                if(isset($array["debuglevel"])) {
                    echo '<div style="overflow-y: auto; height:400px;background-color:rgb(255,255,255);display:inline-block;width:100%;">';
                        echo '<div class="sliders"><input type="range" id="rollof" class="slider" min="0" max="1" step="0.01" /><label for="rollof">roloff</label></div>';
                        echo '<div class="sliders"><input type="range" id="refDistance" class="slider" min="0" max="50" step="0.01" /><label for="refDistance">refDistance</label></div>';
                        echo '<div class="sliders"><input type="range" id="maxDistance" class="slider" min="0" max="120" step="0.01" /><label for="maxDistance">maxDistance</label></div>';
                    echo '</div>';
                }
            ?>
            <?php
                parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
                if(isset($array["debuglevel"])) {
                    echo '<div style="overflow-y: auto; height:400px;background-color:rgb(255,255,255);"><p id="console"></p></div>';
                }
            ?>
        </div> <!-- main div -->
        

        <!-- http://reverbjs.org/ -->
        <script src="http://reverbjs.org/reverb.js"></script> 
        
        <!-- main scripts -->
        <script src="main.js" type="text/javascript"></script>
        <script src="circularslider/circularslider.js"></script>
        
        <?php
            # INCLUDE SCRIPT TO REGISTER DEBUG INTERFACE
            parse_str(parse_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" )["query"], $array);
            if(isset($array["debuglevel"])) {
                echo '<script src="debug.js" type="text/javascript"></script>';
            }
        ?>
    </body>
</html>