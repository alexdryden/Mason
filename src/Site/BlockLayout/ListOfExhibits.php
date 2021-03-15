<?php
namespace Mason\Site\BlockLayout;

use Doctrine\ORM\EntityManager;
use Mason\Form\Element\AllSiteSelect;
use Mason\Form\Element\TeamSelect;
use Mason\Module;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Site\Navigation\Translator;
use Omeka\View\Helper\Api;
use Teams\Form\Element\AllTeamSelect;
use Zend\Db\Sql\Select;
use Zend\Form\Element;
use Zend\Form\Form;
use Omeka\Module\Manager;
use Zend\View\Renderer\PhpRenderer;
use function Sodium\add;

class ListOfExhibits extends AbstractBlockLayout
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Manager
     */
    protected $moduleManager;


//    /**
//     * @var Translator
//     */
//    protected $navTranslator;

    public function __construct(EntityManager $entityManager, Manager $moduleManager)
    {
        $this->entityManager = $entityManager;
        $this->moduleManager = $moduleManager;
//        $this->navTranslator = $navTranslator;
    }


    protected $defaults = [
        'all_exhibits' => false,
        'sibling_sites' => false,
        'child_pages' => null,
        'limit' => null,
        'pagination' => false,
    ];




    public function getLabel()
    {
        return 'List of Exhibits (KSharp)'; // @translate
    }

    function getPreview($page_id, $default, $size, PhpRenderer $view){
        //default thumbnail if the page has no media
        $img = $default;
        $alt = 'Exhibit landing page';

        $page = $view->api()->read('site_pages', ['id' => $page_id])->getContent();

        //get the first media attachment on the target page

                foreach($page->blocks() as $block){
                    if (get_class($block) === 'Omeka\Api\Representation\SitePageBlockRepresentation'){
                        if ($block->attachments()){
                            $media = false;
                            foreach ($block->attachments() as $attachment):
                                if($attachment->media()){
                                    $media = $attachment->media();
                                } elseif ($attachment->item()->primaryMedia()){
                                    $media = $attachment->item()->primaryMedia();
                                }
                                if ($media){
                                    if ($thumbnail = $media->thumbnail()) {
                                        $img = $thumbnail->assetUrl();
                                    } else {
                                        $img = $media->thumbnailUrl($size);
                                    }
                                    if (array_key_exists('o-module-alt-text:alt-text', $media->primaryMedia()->jsonSerialize())
                                        && $media->primaryMedia()->jsonSerialize()['o-module-alt-text:alt-text']
                                    ) {
                                        $alt = $media->primaryMedia()->jsonSerialize()['o-module-alt-text:alt-text'];
                                    } else {
                                        $alt = 'Thumbnail preview for next page';
                                    }
                                    break 2;
                                }
                            endforeach;
                        }
                    }
                }



        $title = $page->title();
        $preview['img_src'] = $img;
        $preview['alt'] = $alt;
        $preview['title'] = $title;
        $preview['url'] = $page->slug();
        $preview['site_page'] = $page;

        return $preview;
    }

    public function getChildPages($block, PhpRenderer $view)
    {

        //TODO: first check to to see if the theme has set a default image
        $default_img = $view->assetUrl('img/Default.png', 'Mason');

        $indents = [];
        $iterate = function ($linksIn, $depth = 0) use (&$iterate, &$indents) {
            foreach ($linksIn as $key => $data) {

                //if we it is a page, then the depth is 0
                if ('page' === $data['type']) {
                    $indents[$data['data']['id']] = $depth;
                }
                if (isset($data['links'])) {
                    $iterate($data['links'], $depth + 1);
                }
            }
        };

        //the docstring is wrong so the autosuggest thinks page() is a SiteRepresentation
        $site = $block->page()->site();

        $iterate($site->navigation());

        $exhibits_depth = $block->dataValue('child_pages');


        $exhibits = [];


        //filter array for values that match given depth
        foreach ($indents as $page_id => $depth):
            if ($depth == $exhibits_depth-1){
                $exhibits[$page_id] = $this->getPreview($page_id, $default_img,'large', $view);
            }
        endforeach;

        return $exhibits;

    }

    public function getSiblingSites(SitePageBlockRepresentation $block, PhpRenderer $view)
    {
        //TODO: first check to to see if the theme has set a default image
        $default_img = $view->assetUrl('img/Default.png', 'Mason');

        $site = $block->page()->site();
        $em = $this->entityManager;
        if ($this->moduleManager->getModule('Teams')){
            //this is not designed for complex team arrangements where sites belong to multiple teams
            $site_team = $em->getRepository('Teams\Entity\TeamSite')
                ->findOneBy(['site'=>$site->id()])->getTeam()->getId();
            $team_sites = $em->getRepository('Teams\Entity\TeamSite')->findBy(['team'=>$site_team]);


            $pages = [];
            foreach ($team_sites as $ts):
                $site =  $view->api()->read('sites', ['id' => $ts->getSite()->getId()])->getContent();
                if ($site->homepage()){
                    $pages[] = $site->homepage();
                } elseif($site->pages()){
                    $p = $site->pages();


                    $pages[] = array_shift($p);
                }
            endforeach;

            $exhibits = [];

            //TODO: should the title really be the title of the homepage or the title of the site?
            foreach ($pages as $page):
                $page_id = $page->id();
                $exhibits[$page_id] = $this->getPreview($page_id, $default_img,'large', $view);
            endforeach;

            return $exhibits;

        }

    }

    public function getAllExhibits($block)
    {

    }

    public function getAllTeams()
    {


    }

    public function form(PhpRenderer $view, SiteRepresentation $site,
                         SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
    ) {

        $data = $block ? $block->data() + $this->defaults : $this->defaults;

        $form = new Form();
//        $form->add([
//            'name'=>'o:block[__blockIndex__][o:data][all_exhibits]',
//            'type' => Element\Checkbox::class,
//            'attributes' => array(
//                'id' => 'list-of-exhibits-include-all',
//            ),
//            'options' => [
//                'label' => 'Include all Omeka-s exhibits', // @translate
//                'info' => 'Will attempt to display all exhibit pages from all sites in Omeka-s',
//            ],
//
//
//        ]);
//        $form->add([
//            'name'=>'o:block[__blockIndex__][o:data][sibling_sites]',
//            'type' => Element\Checkbox::class,
//            'attributes' => array(
//                'id' => 'list-of-exhibits-include-siblings'
//            ),
//            'options' => [
//                'label' => 'Include other sites', // @translate
//                'info' => 'For cases when exhibits are given their own site. If Teams module is installed, exhibit index
//                 will include siblings in the same team. Otherwise, it will include all Omeka-s sites.',
//            ],
//
//        ]);
        $form->add([
            'name'=>'o:block[__blockIndex__][o:data][child_pages]',
            'type' => Element\Number::class,
            'attributes' => array(
                'id' => 'list-of-exhibits-include-children',
                'value' => 1,
                'default' => 1,
                'placeholder' => 'select a value for n',

            ),
            'options' => [
                'label' => 'Include pages that are n-deep in this site', // @translate
                'info' => "In most cases this is 1.",

            ],

        ])->setValue('1');
        $form->add([
            'name'=> 'o:block[__blockIndex__][o:data][include_self]',
            'type' => Element\Checkbox::class,
            'attributes' => array(
                'id' => 'list-of-exhibits-include-self',
                'default' => false,
            ),
            'options' => [
                'label' => 'Include this page in the list of exhibits?', // @translate
                'info' => "Uncommon, usually this should be unchecked.",
            ],
        ]);
//        $form->add([
//            'name' => 'o:block[__blockIndex__][o:data][team]',
//            'type' => Element\Select::class,
//            'options' => [
//                'label' => 'team',
//                'info' => 'just testing out how to use jquery .chosen in this form',
//                'value_options' => [
//                    '0' => 'French',
//                    '1' => 'English',
//                    '2' => 'Japanese',
//                    '3' => 'Chinese',
//                    '4' => 'French',
//                    '5' => 'English',
//                    '6' => 'Japanese',
//                    '7' => 'Chinese',
//                ],
//                'chosen' => true
//            ],
//           'attributes' => [
//               'id' => 'list-of-exhibits-team',
//               'class' => 'chosen-select',
//               'multiple' => true
//           ]
//
//        ]);



        $form->setData([
//            'o:block[__blockIndex__][o:data][all_exhibits]' => $data['all_exhibits'],
            'o:block[__blockIndex__][o:data][sibling_sites]' => $data['sibling_sites'],
            'o:block[__blockIndex__][o:data][child_pages]' => $data['child_pages'],
            'o:block[__blockIndex__][o:data][include_self]' => $data['include_self'],

//            'o:block[__blockIndex__][o:data][limit]' => $data['limit'],
//            'o:block[__blockIndex__][o:data][pagination]' => $data['pagination'],
        ]);

        return $view->formCollection($form, false);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        $exhibits = [];
        $siblings = [];

        if ($block->dataValue('child_pages')){
            $exhibits = $exhibits + $this->getChildPages($block, $view);
        }
        if ($block->dataValue('all_exhibits')){
            $exhibits = $exhibits + $this->getAllExhibits($block);
        }

        if ($block->dataValue('sibling_sites')){
            $siblings = $siblings + $this->getSiblingSites($block, $view);
        }

        $current_page = $block->page()->id();


        return $view->partial('common/block-layout/list-of-exhibits', [
            'exhibits' => $exhibits,
            'siblings' => $siblings,
            'include_self' => $block->dataValue('include_self'),
            'current_page' => $current_page
        ]);
    }
}
