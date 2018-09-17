<template>
    <v-container>
      <div class="layouts">
        <div>
          <ul class="list-group">
            <a id="enableWindowLevelTool" class="list-group-item">WW/WC</a>
            <a id="pan" class="list-group-item">Pan</a>
            <a id="zoom" class="list-group-item">Zoom</a>
            <a id="enableLength" class="list-group-item">Length</a>
            <a id="probe" class="list-group-item">Probe</a>
            <a id="circleroi" class="list-group-item">Elliptical ROI</a>
            <a id="rectangleroi" class="list-group-item">Rectangle ROI</a>
            <a id="angle" class="list-group-item">Angle</a>
            <a id="highlight" class="list-group-item">Highlight</a>
            <a id="freehand" class="list-group-item">Freeform ROI</a>
            <a id="eraser" class="list-group-item">Eraser</a>
          </ul>
        </div>

        <div>
          mobileTools
          <div class="dropdown">
            <button id="toolSelector" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Select tool <span class="caret"></span></button>
            <ul class="dropdown-menu">
              <li class="dropdown-header">Tools</li>
              <li><a id="rotate" class="list-group-item">Rotate</a></li>

              <li><a id="zoomDrag" class="list-group-item">Zoom (drag)</a></li>
              <li><a id="stackScroll" class="list-group-item">Stack Scroll</a></li>
              <li><v-btn id="length">Length</v-btn></li>

            </ul>
            <a id="clearToolData" class="btn btn-secondary">Clear Tools</a>
            <a id="resetViewport" class="btn btn-secondary">Reset View</a>
          </div>
        </div>
        <div style="width:100%;height:100%;min-height:256px;min-width:256px;position:relative;display:inline-block;color:white;"
             oncontextmenu="return false"
             class='cornerstone-enabled-image'
             unselectable='on'
             onselectstart='return false;'
             onmousedown='return false;'>
          <div id="dicomImage" ref="dicomImage"
               style="width:100%;height:100%;min-height:500px;min-width:256px;">
          </div>
          <div id="mrtopleft" style="position: absolute;top:3px; left:3px">
            {{ this.$store.getters.patientName }}
          </div>
          <div id="mrtopright" style="position: absolute;top:3px; right:3px">
            ФДМ - Пловдив
          </div>
          <div id="mrbottomright" style="position: absolute;bottom:3px; right:3px">
            Zoom:
          </div>
          <div id="mrbottomleft" style="position: absolute;bottom:3px; left:3px">
            WW/WC:
          </div>
        </div>
        <v-btn @click="sta(image)">
          enab
        </v-btn>
      </div>
    </v-container>
</template>

<script>
  import Hammer from 'hammerjs'
  import * as cornerstone from 'cornerstone-core';
  import * as cornerstoneMath from 'cornerstone-math';
  import * as cornerstoneWebImageLoader from 'cornerstone-web-image-loader';
  import * as cornerstoneWADOImageLoader from 'cornerstone-wado-image-loader';
  import * as cornerstoneTools from 'cornerstone-tools';
  import * as dicomParser from 'dicom-parser'
  // Specify external dependencies
  // cornerstoneTools.external.Hammer = Hammer
  cornerstoneTools.external.cornerstone = cornerstone;
  cornerstoneWebImageLoader.external.cornerstone = cornerstone;
  cornerstoneWADOImageLoader.external.cornerstone = cornerstone;

  cornerstoneTools.external.cornerstoneMath = cornerstoneMath;
  cornerstoneWADOImageLoader.external.dicomParser = dicomParser

  import axios from 'axios'
  import MobileDetect from 'mobile-detect'

  export default {
    name: 'patientImage',
    data () {
      return {
        image: this.$store.getters.currentImageData,
        IsMobile: false,
      }
    },
    mounted () {
      var md = new MobileDetect(window.navigator.userAgent);
      if(md.mobile()) {
        this.IsMobile = true
      } else {
        this.IsMobile = false
      }

      var element = this.$refs.dicomImage

      // Listen for changes to the viewport so we can update the text overlays in the corner
      function onImageRendered(e) {
        var viewport = cornerstone.getViewport(e.target);
        document.getElementById('mrbottomleft').textContent = "WW/WC: " + Math.round(viewport.voi.windowWidth) + "/" + Math.round(viewport.voi.windowCenter);
        document.getElementById('mrbottomright').textContent = "Zoom: " + viewport.scale.toFixed(2);
      };
      element.addEventListener('cornerstoneimagerendered', onImageRendered);

      var configuration = {
        testPointers: function(eventData) {
          return (eventData.numPointers >= 3);
        }
      };
      cornerstoneTools.panMultiTouch.setConfiguration(configuration);



      var config = {
        // invert: true,
        minScale: 0.25,
        maxScale: 20.0,
        preventZoomOutsideImage: true
      };
      cornerstoneTools.zoom.setConfiguration(config);

      cornerstone.enable(element);

      cornerstone.loadImage(window.location.origin + '/api/amx/' + this.$route.params.instanceId).then(function (image) {
        cornerstone.displayImage(element, image);

        if (md.mobile()) {

          // Set the stack as tool state
          cornerstoneTools.addToolState(element);
          cornerstoneTools.touchInput.enable(element);
          cornerstoneTools.mouseWheelInput.enable(element )

          // Enable all tools we want to use with this element
          cornerstoneTools.zoomTouchPinch.activate(element);
          //cornerstoneTools.rotateTouch.activate(element);
          cornerstoneTools.wwwcTouchDrag.activate(element);
          cornerstoneTools.panMultiTouch.activate(element);
          // helper function used by the tool button handlers to disable the active tool
          // before making a new tool active

          function disableAllTools() {
            cornerstoneTools.panTouchDrag.deactivate(element);
            cornerstoneTools.rotateTouchDrag.deactivate(element);
            cornerstoneTools.rotateTouch.disable(element);
            cornerstoneTools.ellipticalRoiTouch.deactivate(element);
            cornerstoneTools.angleTouch.deactivate(element);
            cornerstoneTools.rectangleRoiTouch.deactivate(element);
            cornerstoneTools.lengthTouch.deactivate(element);
            cornerstoneTools.probeTouch.deactivate(element);
            cornerstoneTools.zoomTouchDrag.deactivate(element);
            cornerstoneTools.wwwcTouchDrag.deactivate(element);
            cornerstoneTools.stackScrollTouchDrag.deactivate(element);
          }
          // Tool button event handlers that set the new active tool
          var toolSelector = document.getElementById('toolSelector');
          document.getElementById('enableWindowLevelTool').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.wwwcTouchDrag.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('pan').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.panTouchDrag.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('rotate').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.rotateTouchDrag.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('zoom').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.zoomTouchPinch.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('zoomDrag').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.zoomTouchDrag.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('stackScroll').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.stackScrollTouchDrag.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('length').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.lengthTouch.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('probe').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.probeTouch.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('circleroi').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.ellipticalRoiTouch.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('rectangleroi').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.rectangleRoiTouch.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('angle').addEventListener('click', function (e) {
            disableAllTools();
            cornerstoneTools.angleTouch.activate(element);
            toolSelector.textContent = e.currentTarget.innerHTML
          });
          document.getElementById('clearToolData').addEventListener('click', function() {
            disableAllTools();
            cornerstoneTools.wwwcTouchDrag.activate(element);
            cornerstoneTools.zoomTouchPinch.activate(element);
            toolSelector.innerHTML = 'Select tool <span class="caret"></span>';
            var toolStateManager = cornerstoneTools.globalImageIdSpecificToolStateManager;
            // Note that this only works on ImageId-specific tool state managers (for now)
            toolStateManager.clear(element);
            cornerstone.updateImage(element);
          });
          document.getElementById('resetViewport').addEventListener('click', function() {
            cornerstone.reset(element);
          });
        } else {
          cornerstoneTools.mouseInput.enable(element);
          cornerstoneTools.mouseWheelInput.enable(element);
          // Enable all tools we want to use with this element
          cornerstoneTools.wwwc.activate(element, 1); // ww/wc is the default tool for left mouse button
          cornerstoneTools.pan.activate(element, 2); // pan is the default tool for middle mouse button
          cornerstoneTools.zoom.activate(element, 4); // zoom is the default tool for right mouse button
          cornerstoneTools.zoomWheel.activate(element); // zoom is the default tool for middle mouse wheel
          cornerstoneTools.probe.enable(element);
          cornerstoneTools.length.enable(element);
          cornerstoneTools.ellipticalRoi.enable(element);
          cornerstoneTools.rectangleRoi.enable(element);
          cornerstoneTools.angle.enable(element);
          cornerstoneTools.highlight.enable(element);
          cornerstoneTools.eraser.enable(element);
          activate("enableWindowLevelTool");

          function activate(id) {
            document.querySelectorAll('a').forEach(function(elem) {
              elem.classList.remove('active');
            });
            document.getElementById(id).classList.add('active');
          }
          // helper function used by the tool button handlers to disable the active tool
          // before making a new tool active
          function disableAllTools()
          {
            cornerstoneTools.wwwc.disable(element);
            cornerstoneTools.pan.activate(element, 2); // 2 is middle mouse button
            cornerstoneTools.zoom.activate(element, 4); // 4 is right mouse button
            cornerstoneTools.probe.deactivate(element, 1);
            cornerstoneTools.length.deactivate(element, 1);
            cornerstoneTools.ellipticalRoi.deactivate(element, 1);
            cornerstoneTools.rectangleRoi.deactivate(element, 1);
            cornerstoneTools.angle.deactivate(element, 1);
            cornerstoneTools.highlight.deactivate(element, 1);
            cornerstoneTools.freehand.deactivate(element, 1);
            cornerstoneTools.eraser.deactivate(element, 1);
          }
          // Tool button event handlers that set the new active tool
          document.getElementById('enableWindowLevelTool').addEventListener('click', function() {
            activate('enableWindowLevelTool')
            disableAllTools();
            cornerstoneTools.wwwc.activate(element, 1);
          });
          document.getElementById('pan').addEventListener('click', function() {
            activate('pan')
            disableAllTools();
            cornerstoneTools.pan.activate(element, 3); // 3 means left mouse button and middle mouse button
          });
          document.getElementById('zoom').addEventListener('click', function() {
            activate('zoom')
            disableAllTools();
            cornerstoneTools.zoom.activate(element, 5); // 5 means left mouse button and right mouse button
          });
          document.getElementById('enableLength').addEventListener('click', function() {
            activate('enableLength')
            disableAllTools();
            cornerstoneTools.length.activate(element, 1);
          });
          document.getElementById('probe').addEventListener('click', function() {
            activate('probe')
            disableAllTools();
            cornerstoneTools.probe.activate(element, 1);
          });
          document.getElementById('circleroi').addEventListener('click', function() {
            activate('circleroi')
            disableAllTools();
            cornerstoneTools.ellipticalRoi.activate(element, 1);
          });
          document.getElementById('rectangleroi').addEventListener('click', function() {
            activate('rectangleroi')
            disableAllTools();
            cornerstoneTools.rectangleRoi.activate(element, 1);
          });
          document.getElementById('angle').addEventListener('click', function () {
            activate('angle')
            disableAllTools();
            cornerstoneTools.angle.activate(element, 1);
          });
          document.getElementById('highlight').addEventListener('click', function() {
            activate('highlight')
            disableAllTools();
            cornerstoneTools.highlight.activate(element, 1);
          });
          document.getElementById('freehand').addEventListener('click', function() {
            activate('freehand')
            disableAllTools();
            cornerstoneTools.freehand.activate(element, 1);
          });
          document.getElementById('eraser').addEventListener('click', function() {
            activate('eraser');
            disableAllTools();
            // In order for the eraser to work, other tools must be in the 'enable'
            // state. This allows eraser to receive mouse click events on other tools'
            // data.
            cornerstoneTools.probe.enable(element, 1);
            cornerstoneTools.length.enable(element, 1);
            cornerstoneTools.ellipticalRoi.enable(element, 1);
            cornerstoneTools.rectangleRoi.enable(element, 1);
            cornerstoneTools.angle.enable(element, 1);
            cornerstoneTools.highlight.enable(element, 1);
            cornerstoneTools.freehand.enable(element, 1);
            cornerstoneTools.eraser.enable(element, 1);
            cornerstoneTools.eraser.activate(element, 1);
          });
        }

      });
    },
    methods: {
      sta(cs) {
        getPixelData(this.image)
      },
      enableIm (instImage) {
        var element = this.$refs.dicomImage

        cornerstone.enable(element)
        // Load & Display
        cornerstone
          .loadImage(instImage)
          .then(function (image) {

            // Now that we've "loaded" the image, we can display it on
            // our Cornerstone enabled element of choice
            cornerstone.displayImage(element, image)

            // We need to enable each way we'd like to be able to receive
            // and respond to input (mouse, touch, scrollwheel, etc.)
            cornerstoneTools.mouseInput.enable(element)
            cornerstoneTools.touchInput.enable(element)

            // Activate mouse tools we'd like to use
            cornerstoneTools.wwwc.activate(element, 1) // left click
            cornerstoneTools.pan.activate(element, 2) // middle click
            cornerstoneTools.zoom.activate(element, 4) // right click

            // Activate Touch / Gesture tools we'd like to use
            cornerstoneTools.wwwcTouchDrag.activate(element) // - Drag
            cornerstoneTools.zoomTouchPinch.activate(element) // - Pinch
            cornerstoneTools.panMultiTouch.activate(element) // - Multi (x2)
          })

      }
    }
  }
</script>

<style scoped>

</style>
