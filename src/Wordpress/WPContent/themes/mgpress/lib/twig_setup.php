<?php

if (!class_exists('MgTwig')) {
    class MgTwig
    {

        protected $blockController;


        public function init()
        {
            add_filter('get_twig', array($this, 'add_to_twig'));
            add_filter('title_if_upper', array($this, 'titleCaseIfUpper'));
            TimberHelper::function_wrapper(array('Timber', 'get_posts'));
        }

        function add_to_twig($twig)
        {
            /* this is where you can add your own function to twig */
            $twig->addFunction('load_menu', new Twig_Function_Function(array($this, 'loadMenu')));
            $twig->addFunction('render_blocks', new Twig_Function_Function(array($this, 'renderBlocks')));
            $twig->addFunction('render_block', new Twig_Function_Function(array($this, 'renderBlock')));
            $twig->addFunction('get_option', new Twig_Function_Function(array($this, 'acfOption')));
            $twig->addFunction('gallery_image_type_title', new Twig_Function_Function(array($this, 'galleryImageTypeTitle')));
            $twig->addFunction('render_form', new Twig_Function_Function(array($this, 'renderForm')));
            $twig->addFunction('uniqid', new Twig_Function_Function(array($this, 'uniqid')));
            $twig->addFilter(new Twig_SimpleFilter('title_if_upper', array($this, 'titleCaseIfUpper')));

            return $twig;
        }

        public function uniqid()
        {
            return uniqid();
        }


        public function wordsExcerpt($content, $wordsNumber)
        {
            $words = str_word_count($content, 1);
            if (!empty($content) && (count($words) > $wordsNumber)) {
                $output = array();
                for ($wordsCounter = 0; $wordsCounter < $wordsNumber; $wordsNumber++) {
                    $output[] = $words[$wordsCounter];
                }

                return implode(' ', $output);
            }
        }

        public static function acfOption($optionName)
        {
            if (function_exists('the_field')) {
                return the_field($optionName, 'option');
            } else {
                return '';
            }
        }

        function loadMenu($menuName)
        {
            return new TimberMenu(($menuName));
        }

        function renderBlocks(array $blocks)
        {
            $return = '';
            foreach ($blocks as $block) {
                $return = $return . $this->renderBlock($block);

            }

            return $return;
        }

        function renderBlock($block)
        {
            return $this->blockController->render($block);
        }

        function titleCaseIfUpper($text)
        {

            $exclude = array("Cvs");
            $return = ucwords(strtolower($text));
            foreach ($exclude as $word){
                $return = str_replace($word, strtoupper($word), $return);
            }

            return $return;
        }
    }

    $mgTwig = new MgTwig();
    $mgTwig->init();
}



