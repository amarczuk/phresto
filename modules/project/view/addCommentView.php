<?php

class addCommentView extends AjaxView {
    
     protected $module = 'project';
    
     protected function _prepare() {
          global $Config;
          $this->setModule();
          $out = array();

          $project = new Project( $_POST['id'] );      

          $comment = $project->addComment( $_POST['comment'] );

          if ( !is_array( $comment ) ) {
               $tpl = new Template();
               $tpl->non_header = true;
               $data = array( 'comments'     => array( $comment->get() ), 
                              'next'         => 'x' . $comment->properties['id'],
                              'next_cls'     => 'hide' );
               $tpl->add( 'commentContents',  $data );
               $cmt = $tpl->Get();
               unset( $tpl );

               $out['prepend'] = array( 'commentsContent' => $cmt );
               $out['run'] = array( '$("#newCommentTxt").val( "" );' );
               $out['error'] = false;
          } else {
               $out['error'] = $comment;
          }


        $Config->Templ->Add( 'inline', json_encode( $out ) );
     }
}
  
