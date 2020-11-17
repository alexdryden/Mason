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
        return 'List of Exhibits (Mason)'; // @translate
    }

    public function getChildPages($depth = 1)
    {

    }

    public function getSiblingSites()
    {

    }

    public function getAllExhibits()
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
        $form->add([
            'name'=>'o:block[__blockIndex__][o:data][all_exhibits]',
            'type' => Element\Checkbox::class,
            'attributes' => array(
                'id' => 'list-of-exhibits-include-all',
            ),
            'options' => [
                'label' => 'Include all Omeka-s exhibits', // @translate
                'info' => 'Will attempt to display all exhibit pages from all sites in Omeka-s',
            ],


        ]);
        $form->add([
            'name'=>'o:block[__blockIndex__][o:data][sibling_sites]',
            'type' => Element\Checkbox::class,
            'attributes' => array(
                'id' => 'list-of-exhibits-include-siblings'
            ),
            'options' => [
                'label' => 'Include other sites', // @translate
                'info' => 'For cases when exhibits are given their own site. If Teams module is installed, exhibit index
                 will include siblings in the same team. Otherwise, it will include all Omeka-s sites.',
            ],

        ]);
        $form->add([
            'name'=>'o:block[__blockIndex__][o:data][child_pages]',
            'type' => Element\Number::class,
            'attributes' => array(
                'id' => 'list-of-exhibits-include-children',
                'placeholder' => 'Select a value for n'

            ),
            'options' => [
                'label' => 'Include pages that are n-deep in this site', // @translate
                'info' => "In most cases this is 1. E.g., this page is your unit homepage, and child pages are the landing
                pages for your unit's exhibits",
            ],

        ]);
        $form->add([
            'name' => 'o:block[__blockIndex__][o:data][team]',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'team',
                'info' => 'just testing out how to use jquery .chosen in this form',
                'value_options' => [
                    '0' => 'French',
                    '1' => 'English',
                    '2' => 'Japanese',
                    '3' => 'Chinese',
                    '4' => 'French',
                    '5' => 'English',
                    '6' => 'Japanese',
                    '7' => 'Chinese',
                ],
                'chosen' => true
            ],
           'attributes' => [
               'id' => 'list-of-exhibits-team',
               'class' => 'chosen-select',
               'multiple' => true
           ]

        ]);



        $form->setData([
            'o:block[__blockIndex__][o:data][all_exhibits]' => $data['all_exhibits'],
            'o:block[__blockIndex__][o:data][sibling_sites]' => $data['sibling_sites'],
            'o:block[__blockIndex__][o:data][child_pages]' => $data['child_pages'],
//            'o:block[__blockIndex__][o:data][limit]' => $data['limit'],
//            'o:block[__blockIndex__][o:data][pagination]' => $data['pagination'],
        ]);

        return $view->formCollection($form, false);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {

        //determine the depth from root of a page in the list of pages
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

        //the docstring is wrong so the autosuggest things page() is a SiteRepresentation
        $site = $block->page()->site();

        $iterate($site->navigation());

        $exhibits_depth = $block->dataValue('child_pages');


        $exhibits = [];

        //filter array for values that match given depth
        foreach ($indents as $page_id => $depth):
            if ($depth == $exhibits_depth){
                $exhibits[$page_id] = $depth;
            }
        endforeach;


        return $view->partial('common/block-layout/list-of-exhibits', [
            'exhibits' => $exhibits,
        ]);
    }
}
