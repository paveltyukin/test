<?php

$router->addRoute("about", new Zend_Controller_Router_Route(
    "about", 
    array(
        "module" => "default", 
        "controller" => "about", 
        "action" => "index"
        )
    )
);


$router->addRoute( "live", new Zend_Controller_Router_Route(
    ":controller/:year/:month", 
    array(
        "module" => "default", 
        "action" => "index", 
        "month" => "auto", 
        "year" => "auto", 
        ), 
    array(
        "month" => "\d{1,2}", 
        "year" => "\d{4}", 
        "controller" => "live|news|events"
        )
    )
);

$router->addRoute( "etc_upload", new Zend_Controller_Router_Route(
    "etc/images/upload/", 
    array(
        "module" => "etc", 
        "controller" => "images", 
        "action" => "upload"
        ), 
    array(
        "action" => "upload|photo|news|logo|review"
        )
    )
);

$router->addRoute( "etc_counters", new Zend_Controller_Router_Route(
    "etc/counters/:action/", 
    array(
        "module" => "etc", 
        "controller" => "counters", 
        ), 
    array(
        "action" => "getlive|gettotal"
        )
    )
);


$router->addRoute( "admin_auth", new Zend_Controller_Router_Route(
    "admin/:controller/:action/",
    array(
        "module" => "admin", 
        "controller" => "index", 
        "action" => "index",         
        ),
    array(
         "controller" => "index|auth",
         "action" => "index|login"
        )
    )
);

$router->addRoute("admin_news", new Zend_Controller_Router_Route(
    "admin/news/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "news", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        )
    )
);                

$router->addRoute( "admin_news_edit", new Zend_Controller_Router_Route(
    "admin/news/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "news", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|delete|addnews|add|save"                
        )
    )
);

$router->addRoute( "admin_events", new Zend_Controller_Router_Route(
    "admin/events/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "events", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        )
    )
);
       

$router->addRoute( "admin_events_edit", new Zend_Controller_Router_Route(
    "admin/events/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "events", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|delete|state|add|addevent|save"                
        )
    )
);

$router->addRoute( "admin_fact", new Zend_Controller_Router_Route(
    "admin/fact/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "fact", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        )
    )
);
       

$router->addRoute( "admin_fact_edit", new Zend_Controller_Router_Route(
    "admin/fact/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "fact", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|delete|state|add|addfact|save"                
        )
    )
);

$router->addRoute( "admin_job", new Zend_Controller_Router_Route(
    "admin/job/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "job", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        )
    )
);
       

$router->addRoute( "admin_job_edit", new Zend_Controller_Router_Route(
    "admin/job/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "job", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|delete|state|add|addjob|save"                
        )
    )
);

$router->addRoute( "admin_images", new Zend_Controller_Router_Route(
    "admin/images/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "images", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        )
    )
);
       

 $router->addRoute( "admin_images_upload", new Zend_Controller_Router_Route(
    "admin/images/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "images", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|new"                
        )
    )
);
    
$router->addRoute( "admin_images_resize", new Zend_Controller_Router_Route(
    "admin/images/resize/:id/:type/",
    array(
        "module" => "admin", 
        "controller" => "images", 
        "action" => "resize",
        ),
    array(
        "id" => "\d+", 
        "type" => "\d+", 
        "action" => "edit|new"                
        )
    )    
);

$router->addRoute( "admin_partners_page", new Zend_Controller_Router_Route(
    "admin/partners/:action/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "partners", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        "action" => "index|places|advertiser"  
        )
    )    
);

$router->addRoute( "admin_partners_edit", new Zend_Controller_Router_Route(
    "admin/partners/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "partners", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|delete|state|add|addpartner|save"                
        )
    )
);

$router->addRoute( "admin_team", new Zend_Controller_Router_Route(
    "admin/team/:pagename/:page/",
    array(
        "module" => "admin", 
        "controller" => "team", 
        "action" => "index",
        "page" => "1",
        "pagename" => "page"
        ),
    array(
        "page" => "\d+",
        "pagename" => "page",
        "action" => "index"  
        )
    )    
);

$router->addRoute( "admin_team_edit", new Zend_Controller_Router_Route(
    "admin/team/:action/:id/",
    array(
        "module" => "admin", 
        "controller" => "team", 
        ),
    array(
        "id" => "\d+", 
        "action" => "edit|delete|add|add2|save"                
        )
    )
);

$router->addRoute( "admin_slider_edit", new Zend_Controller_Router_Route(
    "admin/slider/:action/:id/",
    array(
        "module" => "admin",
        "controller" => "slider",
        ),
    array(
        "id" => "\d+",
        "action" => "delete|add|save"                
        )
    )
);

$router->addRoute( "admin_faq_add", new Zend_Controller_Router_Route(
    "admin/faq/:action/:type/",
    array(
        "module" => "admin",
        "controller" => "faq",
        "type" => 'adv',
        ),
    array(
        "type" => "adv|pub",
        "action" => "addgroup|add"
        )
    )
);

$router->addRoute( "admin_faq", new Zend_Controller_Router_Route(
    "admin/faq/:action/:id/",
    array(
        "module" => "admin",
        "controller" => "faq",
        ),
    array(
        "id" => "\d+|adv|pub",
        "action" => "deletegroup|addquestion|editgroup|editquestion|deletequestion|addq"
        )
    )
);

$router->addRoute( "admin_testimonial", new Zend_Controller_Router_Route(
    "admin/testimonial/:action/:id/",
    array(
        "module" => "admin",
        "controller" => "testimonial",
        ),
    array(
        "id" => "\d+",
        "action" => "delete|edit|add"
        )
    )
);

$router->addRoute( "order_product_action_id", new Zend_Controller_Router_Route(
    "order/:controller/:action/:id/",
    array(
        "module" => "order",
        "controller" => "index",
        "action" => "index",
        ),
    array(
        "id" => "\d+"
        )
    )
);

$router->addRoute( "order_hints", new Zend_Controller_Router_Route(
    "order/product/:action/:type/",
    array(
        "module" => "order",
        "controller" => "product",
        ),
    array(
         "action" => "hint|sites"

        )
    )
);

$router->addRoute( "order_product", new Zend_Controller_Router_Route(
    "order/:controller/:id/",
    array(
        "module" => "order",
        "controller" => "index",
        "action" => "index",
        "id" => ""
        ),
    array(
        "id" => "\d+"
        )
    )
);

$router->addRoute( "pub_index", new Zend_Controller_Router_Route(
    "pub/:controller/:action/",
    array(
        "module" => "pub",
        "controller" => "index",
        "action" => "index",
        ),
    array(

        )
    )
);

$router->addRoute( "adv_index", new Zend_Controller_Router_Route(
    "adv/:controller/:action/",
    array(
        "module" => "adv", 
        "controller" => "index",
        "action" => "index",
        ),
    array(

        )
    )
);

$router->addRoute( "adv_category_date", new Zend_Controller_Router_Route(
    "adv/category/:date/",
    array(
        "module" => "adv",
        "controller" => "category",
        "action" => "index",
        "date" => 'auto'
        ),
    array(
        "date" => '([\d]{4})\.([\d]{2})'
        )
    )
);