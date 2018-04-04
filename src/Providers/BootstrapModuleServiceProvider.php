<?php namespace WebEd\Plugins\CustomFields\Providers;

use Illuminate\Support\ServiceProvider;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;

class BootstrapModuleServiceProvider extends ServiceProvider
{
    protected $module = 'WebEd\Plugins\CustomFields';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->booted(function () {
            $this->booted();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    protected function booted()
    {
        /**
         * Register to dashboard menu
         */
        \DashboardMenu::registerItem([
            'id' => 'webed-custom-fields',
            'priority' => 20.1,
            'parent_id' => null,
            'heading' => null,
            'title' => 'Custom fields',
            'font_icon' => 'icon-briefcase',
            'link' => route('admin::custom-fields.index.get'),
            'css_class' => null,
            'permissions' => ['view-custom-fields'],
        ]);

        $this->registerUsersFields();
        $this->registerPagesFields();
        $this->registerBlogFields();
    }

    protected function registerUsersFields()
    {
        custom_field_rules()->registerRule('Other', 'Logged in user', 'logged_in_user', function () {
            $userRepository = app(\WebEd\Base\Users\Repositories\Contracts\UserRepositoryContract::class);

            $users = $userRepository->get();

            $userArr = [];
            foreach ($users as $user) {
                $userArr[$user->id] = $user->username . ' - ' . $user->email;
            }

            return $userArr;
        })
            ->registerRule('Other', 'Logged in user has role', 'logged_in_user_has_role', function () {
                $repository = app(\WebEd\Base\ACL\Repositories\Contracts\RoleRepositoryContract::class);

                $roles = $repository->get();

                $rolesArr = [];
                foreach ($roles as $role) {
                    $rolesArr[$role->id] = $role->name . ' - (' . $role->slug . ')';
                }

                return $rolesArr;
            });
    }

    protected function registerPagesFields()
    {
        custom_field_rules()->registerRule('Basic', 'Page template', 'page_template', get_templates('Page'))
            ->registerRule('Basic', 'Page', 'page', function () {
                $pageRepository = $this->app->make(\WebEd\Base\Pages\Repositories\Contracts\PageContract::class);
                $pages = $pageRepository->get();
                $pageArray = [];
                foreach ($pages as $row) {
                    $pageArray[$row->id] = $row->title;
                }
                return $pageArray;
            })
            ->registerRule('Other', 'Model name', 'model_name', [
                'page' => 'Page'
            ]);
    }

    protected function registerBlogFields()
    {
        if (plugins_support()->isActivated('webed-blog') && plugins_support()->isInstalled('webed-blog')) {
            custom_field_rules()->registerRuleGroup('Blog')
                ->registerRule('Blog', 'Post template', 'blog.post_template', get_templates('Post'))
                ->registerRule('Blog', 'Category template', 'blog.category_template', get_templates('Category'))
                ->registerRule('Blog', 'Category', 'blog.category', function () {
                    $categories = get_categories();

                    $categoriesArr = [];
                    foreach ($categories as $row) {
                        $categoriesArr[$row->id] = $row->indent_text . $row->title;
                    }
                    return $categoriesArr;
                })
                ->registerRule('Blog', 'Posts with related category', 'blog.post_with_related_category', function () {
                    $categories = get_categories();

                    $categoriesArr = [];
                    foreach ($categories as $row) {
                        $categoriesArr[$row->id] = $row->indent_text . $row->title;
                    }
                    return $categoriesArr;
                })
                ->registerRule('Blog', 'Post with related category template', 'blog.post_with_related_category_template', get_templates('Category'))
                ->registerRule('Other', 'Model name', 'model_name', [
                    'blog.post' => '(Blog) Post',
                    'blog.category' => '(Blog) Category',
                ]);
        }
    }
}
