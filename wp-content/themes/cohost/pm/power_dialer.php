<?php
/*
Description: add pages to the admin panel for sales training and reviewing sales calls
Version: 0.1
*/


//add_action('admin_menu', 'lead_admin_menu');
 
// Sales training & sales call 
function sales_admin_menu() {
    add_menu_page(
        'Sales Training',
        'Sales Training',
        'lbs_view_admin_pages',
        'sales-training',
        'sales_training_pg',
        '',
        2
    );

    
    add_submenu_page(
        'sales-training', //parent slug
        'Sales Calls', //menu name
        'Sales Calls', //menu name
        'lbs_view_admin_pages', // capability
        'sales-call', // url slug
        'sales_call_pg', //callback function 
        3
    );

     add_submenu_page(   
        'sales-training', //parent slug
        'Systems Training', //page name
        'Systems Training', //menu name
        'lbs_view_admin_pages', // capability required for this menu to be displayed to user
        'systems-training', // url slug
        'systems_training_pg', //callback function 
        '',
        1 //menu order
    );



   
 

}
add_action('admin_menu', 'sales_admin_menu');

 

function sales_training_pg() {

    ?>

    <div class="wrap">
        
        <h1>Power Dialer</h1>
        <p>Welcome to our power dialer position. Below you will find our scripts and trainings </p>


        <p>For more info on the Power Dialer position, please go to our notion page:</p>

         <p><a target="_BLANK" href="https://habitual-airbus-6d2.notion.site/Power-Dialer-Position-129e540782c18036b209e91e56c3ca5a?pvs=74">https://habitual-airbus-6d2.notion.site/Power-Dialer-Position-129e540782c18036b209e91e56c3ca5a?pvs=74</a></p>

        <p>&nbsp;</p> <p>&nbsp;</p>

        <h1>Handling Objections - cold call & sales call</h1>
        <p>
            <iframe src="https://drive.google.com/file/d/10B9xBnK5uAks6MG3TeO7oADE6dGNxg3g/preview" width="640" height="480" allow="autoplay"></iframe>
        </p> 

        <h1>Cold Call Training - 1hr training</h1>
        <p>
            <iframe src="https://drive.google.com/file/d/1P500wVMVnuOm2BobV1LxcA3qaEMWpsyq/preview" width="640" height="480" allow="autoplay"></iframe>
        </p> 

        <h1>How to negotiate the mgmt fee</h1>
        <p>
            <iframe src="https://drive.google.com/file/d/19up2OGqZTlpjytUGCQjHFPMj1P-jzLci/preview" width="640" height="480" allow="autoplay"></iframe>
        </p> 


        
        <h1>Prepare for cold calling</h1>
        <p>
            <iframe src="https://drive.google.com/file/d/1P500wVMVnuOm2BobV1LxcA3qaEMWpsyq/preview" width="640" height="480" allow="autoplay"></iframe>
        </p> 


        <h1>Training VA for cold calling</h1>
        <p>
            <iframe src="https://drive.google.com/file/d/1Cff9XwK0d-vJwcyj9-tx5gRmin9Shm_D/preview" width="640" height="480" allow="autoplay"></iframe>
        </p> 



        <h1>STR-Business-Ultimate-Guide</h1>
    
        <p> Written by Hospitable, highly recommended reading for those who want to learn more about the STR industry. This may also give you insights to answer a client's questions. </p> 
    
        <p><a target="_BLANK" href=" https://drive.google.com/file/d/1pggtkz2IsHj7JeJM-7iZvVkj0VC8TwLS/view?usp=drive_link">https://drive.google.com/file/d/1pggtkz2IsHj7JeJM-7iZvVkj0VC8TwLS/view?usp=drive_link</a></p>


        
    </div>

    <?php 
 
}

function sales_call_pg () {

    $salesCall = array( 
       '14/6/4.5 | DE | Skeptical Mike Part 01' => array(
            'transcript' => 'https://docs.google.com/document/d/1PS0wcihEOrL72O0nKxaXf6J8Cs9aKGZP/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true', 
            'embedCode' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/mjRm3vOO0gU?si=UyOBeZfT3N03ZOMY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
        ), 

        '14/6/4.5 | DE | Skeptical Mike Part 02' => array(
            'transcript' => 'https://docs.google.com/document/d/1bnqojx5d51o46O0WcwJqYbNYZdw8QpqE/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true',
              'embedCode' => ' <iframe src="https://drive.google.com/file/d/1Rpj34zTLsQ_BKB9XuZnUZ2C3GzW36SEc/preview" width="640" height="480" allow="autoplay"></iframe> '
        ), 

 
        '8/4/2.5 | PA | Rosemary' => array(
            'transcript' => '#',
            'embedCode' => ' <iframe src="https://drive.google.com/file/d/1YItdTMEqdMTmyi_EYk-LaO8fPfEZ9f3b/preview" width="640" height="480" allow="autoplay"></iframe>        '
        ),

        '10/3/2 | PA | Jennifer' => array(
            'transcript' => 'https://docs.google.com/document/d/1LccaHhGbqcdmbJcQjljhk3-jA29fYTLd/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true',
            'embedCode' => '
            <iframe src="https://drive.google.com/file/d/17HpVn2AMg6dOJsgwKjd22m_fa7mwtayK/preview" width="640" height="480" allow="autoplay"></iframe> '
        ),


        '10/4/3.5 | PA | Cesarina ' => array(
            //'transcript' => 'https://docs.google.com/document/d/1Agon-m_JgLqgdf0oT5xq6xwWC0t1F5VE/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true',
            'embedCode' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/cQTdDgKlZs4?si=do3F8KimYdAObNkW" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> '
        ),

        '11/3/2.5 | PA | Jolanta' => array(
            //'transcript' => 'https://docs.google.com/document/d/1somB-IeHXH-4x385Nl4e5a_NcX7Wv7zpumzausV-ZK4/edit?usp=sharing',
            'embedCode' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/YEnUAD3cdek?si=PLoFTAfX9BQtne2v" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
        ),

        '5/3/1 | PA | Ellen Cooper' => array(
            'transcript' => 'https://docs.google.com/document/d/1sZ0bg_MHp7MlTT0qnbEncSyU1rqN23s1/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true',
            'embedCode' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/GXqMrN2KzCI?si=40xDlmsDQykcKMTY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
        ),

        '11/4/2.5 | PA | Bruce (Practice) 1' => array(
            'transcript' => 'https://docs.google.com/document/d/12bLEnpaJqnu8-gGFtAL2aESvt1HLed5T/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true',
            'embedCode' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/5hD6tbg6gsg?si=8vUU5i38uqLPKRAx" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
        ),

        '11/4/2.5 | PA | Bruce (Practice) 2' => array(
            'transcript' => 'https://docs.google.com/document/d/1x7Yuqxe7JQgx5aH-DlfubmXBIXyUHktQ/edit?usp=drive_link&ouid=116706145687298652824&rtpof=true&sd=true',
            'embedCode' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/K78pWkvlLrc?si=AlXZBYbYHh00sCpr" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
        ),
    );

    ?>


    <div class="wrap">
        
    <h1>Sales Calls</h1>

    <p>On average, these videos are 1 hour each. If you do not want to watch them all, you can read the transcript instead. <br /> However, the best way to study is to watch the video with the transcript open on the side. </p>

    <p>
    All meeting notes & videos in google drive <a target="_BLANK" href="https://drive.google.com/drive/folders/1mrN33hPafKz0gvxsDFuBY6pplxaaLoYU?usp=sharing">https://drive.google.com/drive/folders/1mrN33hPafKz0gvxsDFuBY6pplxaaLoYU?usp=sharing</a>
    </p>


    <?php

        foreach($salesCall as $name => $sc) {

            $transcriptLink = '';
            if (!empty($sc['transcript']) && $sc['transcript'] !== '#') {
                $transcriptLink = '<p><a href="'.$sc['transcript'].'" target="_BLANK">Transcript & Notes</a></p>';
            }

            echo '<p>&nbsp;</p><h2>'.$name.'</h2>
            
            <p>'.$sc['embedCode'].'</p> 
            
            '.$transcriptLink.'
    ';
        }

        ?>
     
    </div>

    <?php
}

function systems_training_pg() {
    ?>

    <div class="wrap">
    <h1>Systems Training</h1>

    
    <p>For more info on the Power Dialer position, please go to our notion page:</p>

    <p><a target="_BLANK" href="https://habitual-airbus-6d2.notion.site/Power-Dialer-Position-129e540782c18036b209e91e56c3ca5a?pvs=74">https://habitual-airbus-6d2.notion.site/Power-Dialer-Position-129e540782c18036b209e91e56c3ca5a?pvs=74</a></p>



    <p>Cold Call Scripts</p>

    <p><a target="_BLANK" href="https://drive.google.com/drive/folders/1vYQLa272dzjUdnllnQyAd-0k-mgxkuMJ?usp=drive_link">https://drive.google.com/drive/folders/1vYQLa272dzjUdnllnQyAd-0k-mgxkuMJ?usp=drive_link</a></p>
 

    <h1>How to use Close</h1>

 
    <h2>Email Workflows - Drip Campaign</h2>
    <iframe src="https://drive.google.com/file/d/1T_L0PoSOhIz2od2OWfZijpnfrvesGhEV/preview" width="640" height="480" allow="autoplay"></iframe>
     

    <h2>How to use Future Scheduler</h2>
    <iframe src="https://drive.google.com/file/d/1obwYlyHZRKKvHTvWkHH_KKzikzKiFLIY/preview" width="640" height="480" allow="autoplay"></iframe>

    <h2>Backup Phone & Email</h2>
    <iframe src="https://drive.google.com/file/d/1DGOFOlWO7z8v7UkadkIXPyDqf4qLy8yi/preview" width="640" height="480" allow="autoplay"></iframe>


    <h2>Cold Calling SOP</h2>
    <iframe src="https://drive.google.com/file/d/1AONAwZcZjywVX4dC1Gd8Uv0ZQMiq4CXX/preview" width="640" height="480" allow="autoplay"></iframe>


    <h2>Calendly 1</h2>
    <iframe src="https://drive.google.com/file/d/1Hp-M2G8TCkfmCdRJv0d9_8BChF9diEac/preview" width="640" height="480" allow="autoplay"></iframe>


    <h2>Calendly 2</h2>
    <iframe src="https://drive.google.com/file/d/1_jXfHuRpwiqKDOZcjD8RzUL94cdii_u6/preview" width="640" height="480" allow="autoplay"></iframe>


    <h2>Auto dialer aka power dialer</h2>
    <iframe src="https://drive.google.com/file/d/10KQfLnCEoBaiklSL9ysMxWwZLqRtNC1u/preview" width="640" height="480" allow="autoplay"></iframe>

        

<?php 
}

?>