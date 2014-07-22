<div class="slideshowcontainer">
  
      <div class="slider-button" onclick="ss.move(-1)">«</div>
      
      <div id="slideshow">
        
        <ul id="slides" style="overflow: hidden;">
            
            <li class="slide" id="slide6" style="position: absolute; opacity: 1; z-index: 19;">
            	<img src="<?php echo get_template_directory_uri(); ?>/images/banner6.jpg">
                <div class="slideinner">
                    <h1>Professional yet personal</h1>
                    <p>We help create a conscious and happy culture at the workplace by working to improve every interaction, every relationship, every meeting, every project, one individual, one team and one organization at a time. We take our work seriously. We don't take ourselves seriously.</p>
                    <p align="right"><a href="http://thegamechangers.co.in/about-us.php?panel=raison-d-etre" style="color:#FFF;">More</a></p>
                </div>
            </li>
            
            <li class="slide" id="slide1" style="position: absolute; opacity: 1; z-index: 20;">
            	<img src="<?php echo get_template_directory_uri(); ?>/images/banner1.jpg">
                <div class="slideinner">
                    <h1>Outcome oriented. Contextualized. Measurable.</h1>
                    <p>We help businesses deliver superior results measurably and consistently by building, managing, institutionalizing and taking ownership of their learning and development ecosystems.</p>
                    <p align="right"><a href="http://thegamechangers.co.in/the-360-degree-consulting-suite.html" style="color:#FFF;">More</a></p>
                </div>
            </li>
            
            <li class="slide" id="slide2" style="position: absolute; opacity: 1; z-index: 21;">
            	<img src="<?php echo get_template_directory_uri(); ?>/images/banner2.jpg">
                <div class="slideinner">
                    <h1>Learn. Unlearn. Relearn.</h1>
                    <p>With our life skills training we help young minds grow and become well rounded individuals by enabling transfer and application of learning to different contexts.</p>
                    
                    <p align="right"><a href="http://thegamechangers.co.in/the-life-skills-suite.html" style="color:#FFF;">More</a></p>
                </div>
            </li>
            
            <li class="slide" id="slide3" style="position: absolute; opacity: 1; z-index: 16;">
            	<img src="<?php echo get_template_directory_uri(); ?>/images/banner3.jpg">
                <div class="slideinner">
                    <h1>The Power of Perspective</h1>
                    <p>We help our customers differentiate and delight their customers by creating a brand-aligned service culture, thereby building a stronger value proposition and brand story.</p>
                     <p align="right"><a href="http://thegamechangers.co.in/the-service-suite.html" style="color:#FFF;">More</a></p>
                </div>
            </li>
            
            <li class="slide" id="slide4" style="position: absolute; opacity: 1; z-index: 17;">
            	<img src="<?php echo get_template_directory_uri(); ?>/images/banner4.jpg">
                <div class="slideinner">
                    <h1>Experiential Learning</h1>
                    <p>Our range of experiential learning workshops galvanise students and professionals to learn, internalise, retain and act helping both job performance and career growth.</p>
                    <p align="right"><a href="http://thegamechangers.co.in/the-experiential-learning-suite.html" style="color:#FFF;">More</a></p>
                </div>
            </li>
            
            <li class="slide" id="slide5" style="position: absolute; opacity: 1; z-index: 18;">
            	<img src="<?php echo get_template_directory_uri(); ?>/images/banner5.jpg">
                <div class="slideinner">
                    <h1>What? Why? How?</h1>
                    <p>We help individuals, teams and organisations ask the right questions - not just the what, but also the why and the how - and collaborate in addressing and embracing complexity.</p>
                    <p align="right"><a href="http://thegamechangers.co.in/about-us.php?panel=differentiators" style="color:#FFF;">More</a></p>
                </div>
            </li>
        
        </ul>
  
      <ul id="pagination" class="pagination">
        <li onclick="ss.pos(0)" class=""></li>
        <li onclick="ss.pos(1)" class=""></li>
        <li onclick="ss.pos(2)" class="current"></li>
        <li onclick="ss.pos(3)" class=""></li>
        <li onclick="ss.pos(4)" class=""></li>
        <li onclick="ss.pos(5)" class=""></li>
      </ul>
      
      <script>
        var ss = new TINY.fader.init('ss', {
            id: 'slides',
            auto: 9,
            resume: true,
            navid: 'pagination',
            activeClass: 'current',
            visible: false,
            position: 0
        });
      </script>
      
      </div>
      
      <div class="slider-button" onclick="ss.move(1)">»</div>
      
  </div>