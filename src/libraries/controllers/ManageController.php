<?php
/**
  * Manage controller for HTML endpoints.
  *
  * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class ManageController extends BaseController
{
  /**
    * Call the parent constructor
    *
    * @return void
    */
  public function __construct()
  {
    parent::__construct();
    $this->photo = new Photo;
    $this->theme->setTheme(); // defaults
    if(stristr($_SERVER['REQUEST_URI'], '/manage/apps/callback') === false)
      getAuthentication()->requireAuthentication();
  }

  public function apps()
  {
    $credentialsResp = $this->api->invoke('/oauth/list.json');
    $credentials = $credentialsResp['result'];
    $navigation = $this->getNavigation('apps');
    $bodyTemplate = sprintf('%s/manage-apps.php', $this->config->paths->templates);
    $body = $this->template->get($bodyTemplate, array('credentials' => $credentials, 'navigation' => $navigation, 'crumb' => getSession()->get('crumb')));
    $this->theme->display('template.php', array('body' => $body, 'page' => 'manage-apps'));
  }

  public function appsCallback()
  {
    $this->route->redirect('/manage/apps?m=app-created');
  }

  public function home()
  {
    $groups = getDb()->getGroups(getDb()->getOwner());
    //$filters['groups'] = $groups;
    $photosApiParams = array('_GET' => array_merge($_GET, array('returnSizes' => '160x160xCR', 'pageSize' => 18, 'groups' => $groups)));
    $photosResp = $this->api->invoke('/photos/list.json', EpiRoute::httpGet, $photosApiParams);
    $photos = $photosResp['result'];
#print_r($photos);
    $pages = array('pages' => array());
    if(!empty($photos))
    {
      $pages['pages'] = $this->utility->getPaginationParams($photos[0]['currentPage'], $photos[0]['totalPages'], $this->config->pagination->pagesToDisplay);
      $pages['currentPage'] = $photos[0]['currentPage'];
      $pages['totalPages'] = $photos[0]['totalPages'];
      $pages['requestUri'] = $_SERVER['REQUEST_URI'];
    }
    $pagination = $this->theme->get('partials/pagination.php', $pages);
    $navigation = $this->getNavigation('home');

    $bodyTemplate = sprintf('%s/manage.php', $this->config->paths->templates);
    $body = $this->template->get($bodyTemplate, array('photos' => $photos, 'pagination' => $pagination, 'navigation' => $navigation, 'crumb' => getSession()->get('crumb')));
    $this->theme->display('template.php', array('body' => $body, 'page' => 'manage'));
  }

  public function groups()
  {
    $groupsResp = $this->api->invoke('/groups/list.json');
    $groups = $groupsResp['result'];
    $navigation = $this->getNavigation('groups');
    $bodyTemplate = sprintf('%s/manage-groups.php', $this->config->paths->templates);
    $body = $this->template->get($bodyTemplate, array('groups' => $groups, 'navigation' => $navigation, 'crumb' => getSession()->get('crumb')));
    $this->theme->display('template.php', array('body' => $body, 'page' => 'manage-groups'));
  }

  private function getNavigation($page)
  {
    $tpl = sprintf('%s/manage-navigation.php', $this->config->paths->templates);
    return $this->template->get($tpl, array('page' => $page));
  }
}
