JQueryAjaxBundleEnx for Symfony2.
fork of mabs/jquery-ajax-bundle

## Install
To install this bundle on your project, add this line to composer.json file:

`json
   "analyzer666/jquery-ajax-bundle-enx": "dev-master"`

How to use:

  ...Prepare to use Ajax...

  On client side:
    1) Twig: Making some div which will show before ajax request is go on the server and will hiding after get respone:

      <div id="ajax-loading">
          <img src="{{ asset('bundles/app/images/ajax-loading.gif') }}" class="ajax-loader">
      </div>
    2) Twig: Adding div to update data. Content of this div will be replaced by returned data
        <div id="galleries" class="col-sm-12 col-md-12"></div>    

    3) CSS: Adding CSS styles to this div
    
    `#ajax-loading {  
          position:   fixed;
          z-index:    1000;
          top:        0;
          left:       0;
          height:     100%;
          width:      100%;
          background-color:#c5523f;
          opacity: .8;
          display: none;
       }
      .ajax-loader {
          position: absolute;
          left: 50%;
          top: 50%;
          margin-left: -32px; /* -1 * image width / 2 */
          margin-top: -32px;  /* -1 * image height / 2 */
          display: block;     
      }`
Thats all preparing for client side.

On server side:

1) Controller: Making controller to get response

`/**
  * @Route("/gallery/get/galleries/{category_id}", name="gallery_renderGalleries")
  */
 public function getGalleriesAction($category_id = null)
    {
        $selectedCategory = $this->getDoctrine()
            ->getRepository('AppBundle:GalleryCategory')
            ->findOneById($category_id)
        ;
        $galleries = $selectedCategory->getGalleries();
        if (count($galleries)>1) {
            return $this->render(
                'default/gallery/render_galleries.html.twig', 
                array(
                    'selectedCategory' => $selectedCategory,
                )
            );
        } elseif (count($galleries)==1) {
           return $this->getGalleryPhotosAction($galleries->first()->getId());
        }
 }`

Thats all for server part.

Lets use Ajax! Just insert into the twig this code:

`{{ ja_link({
    'update': '#galleries', 
    'url': url('gallery_renderGalleries', {'category_id':category.id}), 
    'text': 'Gallery',
    'after': set_after,
    'loading': '#ajax-loading'
  }) }}`

  this code get us working link to send ajax request. 

`<a href="http://127.0.0.1/aorig/app_dev.php/gallery/get/galleries/1"
     onclick="$.ajax({
      url: 'http://127.0.0.1/aorig/app_dev.php/gallery/get/galleries/1',
      type: 'POST',
      dataType: 'html',
      beforeSend: function(){$('#ajax-loading').show(); },
      success: function( data ){ 
        $('#galleries').html(data); 
        $('#ajax-loading').hide(); }
      });
      return false;">PhotoGallery</a>`

This wirking example of using this plagin.

This bundle add 3 Twig functions:

##1 - ja_request:
  To generate a js code to send an ajax request:
  
`twig
  {{ ja_request({'update': '#reponse', 'url': path('new')  }) }}
`
  or
`twig
{{
  ja_request({
    'update': '#reponse', 
    'url': path('new'), 
    'after': 'alert("after");', 
    'before': 'alert("before");', 
    'complete': 'alert("complete");'  }) 
}}
`
  => html
`
  $.ajax({ 
    url: "/app_dev.php/new", 
    type: "POST", 
    dataType: "html",
    beforeSend: function(){alert("before");},
    success: function( data ){$( "#reponse" ).html(data);alert("after");}
  });
`
  Options for jQuery ajax request:
`
  *    -$options['type']          : type: '...'; default - POST
  *    -$options['dataType']      : dataType: '$dataType'; default - html 
  *    +$options['url']           : url: "..."
  *    -$options['before']        : beforeSend: function(){"..."}
  *    +$options['update']        : success: function( data ){"...").html(data)
  *    -$options['after']         : adding to the end of success:
  *    -$options['complete']      : complete: function(){"..."}
  *    -$options['loading']       : div id to show and hideand hide
`

##2 - ja_link:

  To generate a link:
  
`twig
  {{ ja_link({'update': '#reponse', 'url': path('new'), 'text': 'new link'  }) }}
`
  To add a confirm action on click, you just have to use 'confirm': true, by default the text is "Are you sure you want to perform this action?"
  then if you want to replace it, use 'confirm_msg': "***".

  You can also use those parameters 'before' and 'after' to execute JS code.
  Lets upderstand all options:

    Options for link (<a href>/<a>) element:
`
     *   -$options['confirm']        : true-false:
     *   -$options['confirm_msg']    : message to confirm ajax request
     *   -$options['class']          : <a class="..."
     *   -$options['id']             : <a id="..."
     *   +$options['url']            : <a href="..."
     *   +$options['text']           : <a>...</a>
`
=>
`
  {{ ja_link({
    'update': '#photos', 
    'url': url('gallery_renderGalleryPhotos', {'gallery_id':gallery.id}), 
    'text': gallery.name,
    'after': unitegallery_function,
    'loading': 'ajax-loading'
  }) }}
`

##3 - ja_buttton

  Options of button elements:
`
     *   -$options['confirm']        : true-false:
     *   -$options['confirm_msg']    : message to confirm request
     *   -$options['class']          : <button class="..."
     *   -$options['id']             : <button id="..."
     *   -$options['type']           : <button type="..."
     *   -$options['data-toggle']    : <button data-toggle="..."
     *   -$options['data-target']    : <button data-target="..."
     *   -$options['data-dismiss']   : <button data-dismiss="..."
     *   -$options['aria-label']     : <button aria-label="..."
     *   +$options['text']           : <button>...</button>
`
  jQuery options same like in ja_link function:

##License
>  This bundle is available under the [MIT license](LICENSE).

