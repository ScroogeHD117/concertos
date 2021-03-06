<?php
/**
 * NOTICE OF LICENSE
 *
 * UNIT3D is open-sourced software licensed under the GNU General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Forum;
use App\Group;
use App\Permission;
use \Toastr;

class ForumController extends Controller
{

    /**
     * Show Forums
     *
     */
    public function index()
    {
        $categories = Forum::where('parent_id', 0)->get();

        return view('Staff.forum.index', ['categories' => $categories]);
    }

    /**
     * Add A Forum
     *
     */
    public function add(Request $request)
    {
        $categories = Forum::where('parent_id', 0)->get();
        if ($request->isMethod('POST')) {
            $parentForum = Forum::findOrFail($request->input('parent_id'));
            $forum = new Forum();
            $forum->name = $request->input('title');
            $forum->position = $request->input('position');
            $forum->slug = str_slug($request->input('title'));
            $forum->description = $request->input('description');
            $forum->parent_id = ($request->input('forum_type') == 'category') ? 0 : $parentForum->id;
            $forum->save();

            return redirect()->route('staff_forum_index')->with(Toastr::success('Forum has been created successfully', 'Yay!', ['options']));
        }
        return view('Staff.forum.add', ['categories' => $categories]);
    }

    /**
     * Edit A Forum
     *
     *
     */
    public function edit(Request $request, $id)
    {
        $categories = Forum::where('parent_id', 0)->get();
        $forum = Forum::findOrFail($id);
        if ($request->isMethod('POST')) {
            $forum->name = $request->input('title');
            $forum->position = $request->input('position');
            $forum->slug = str_slug($request->input('title'));
            $forum->description = $request->input('description');
            $forum->parent_id = ($request->input('forum_type') == 'category') ? 0 : $request->input('parent_id');
            $forum->parent_id = $request->input('parent_id');
            $forum->save();

            return redirect()->route('staff_forum_index')->with(Toastr::success('Forum has been edited successfully', 'Yay!', ['options']));
        }
        return view('Staff.forum.edit', ['categories' => $categories, 'forum' => $forum]);
    }

    /**
     * Delete A Forum
     *
     *
     */
    public function delete($id)
    {
        // Forum to delete
        $forum = Forum::findOrFail($id);

        $permissions = Permission::where('forum_id', $forum->id)->get();
        foreach ($permissions as $p) {
            $p->delete();
        }
        unset($permissions);

        if ($forum->parent_id == 0) {
            $category = $forum;
            $permissions = Permission::where('forum_id', $category->id)->get();
            foreach ($permissions as $p) {
                $p->delete();
            }

            $forums = $category->getForumsInCategory();
            foreach ($forums as $forum) {
                $permissions = Permission::where('forum_id', $forum->id)->get();
                foreach ($permissions as $p) {
                    $p->delete();
                }

                foreach ($forum->topics as $t) {
                    foreach ($t->posts as $p) {
                        $p->delete();
                    }
                    $t->delete();
                }
                $forum->delete();
            }
            $category->delete();
        } else {
            $permissions = Permission::where('forum_id', $forum->id)->get();
            foreach ($permissions as $p) {
                $p->delete();
            }
            foreach ($forum->topics as $t) {
                foreach ($t->posts as $p) {
                    $p->delete();
                }
                $t->delete();
            }
            $forum->delete();
        }
        return redirect()->route('staff_forum_index')->with(Toastr::success('Forum has been deleted successfully', 'Yay!', ['options']));
    }
}
