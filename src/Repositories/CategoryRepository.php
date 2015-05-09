<?php
namespace Lasallecms\Lasallecmsapi\Repositories;

/**
 *
 * Internal API package for the LaSalle Content Management System, based on the Laravel 5 Framework
 * Copyright (C) 2015  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    Internal API package for the LaSalle Content Management System
 * @version    1.0.0
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\BaseRepository;
use Lasallecms\Lasallecmsapi\Models\Category;

// Laravel facades
use Illuminate\Support\Facades\Auth;

class CategoryRepository extends BaseRepository
{
    /*
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Category
     */
    protected $model;


    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Category
     */
    public function __construct(Category $model)
    {
        $this->model = $model;
    }


    /*
     * Display all the categories in the admin listing
     *
     * @return collection
     */
    public function allCategoriesForDisplayOnAdminListing()
    {
        return $this->model->orderBy('title', 'ASC')->get();
    }
    /*
     * What is the category's ID for a given category slug?
     *
     * @param $categorySlug  text  The category's slug
     * @return integer
     */
    public function findCategoryIdByTitle($categoryTitle)
    {
        $category = $this->model
            ->where ('title', '=', $categoryTitle)
            ->get()
            ->toArray();
        return $category[0]['id'];
    }


    /*
     * Find all the post records associated with a category
     *
     * @param id  $id
     * @return int
     */
    public function countAllPostsThatHaveCategoryId($id)
    {
        return count($this->model->find($id)->post);
    }


    /*
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createCategory($data)
    {
        $category = new Category();

        $category->title       = $data['title'];
        $category->description = $data['description'];
        $category->parent_id   = $data['parent_id'];
        $category->created_by  = Auth::user()->id;
        $category->updated_by  = Auth::user()->id;

        return $category->save();
    }

    /*
     * UPDATE
     *
     * @param  array  $data
     * @return bool
     */
    public function updateCategory($data)
    {
        $category = $this->getFind($data['id']);
        $category->description = $data['description'];
        $category->parent_id   = $data['parent_id'];
        return $category->save();
    }
}