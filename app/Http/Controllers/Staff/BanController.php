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
use Illuminate\Support\Facades\Mail;
use App\Mail\BanUser;
use App\Mail\UnbanUser;
use App\User;
use App\Ban;
use Carbon\Carbon;
use \Toastr;

class BanController extends Controller
{
    public function getBans()
    {
        $bans = Ban::latest()->paginate(25);

        return view('Staff.bans.index', ['bans' => $bans]);
    }

    /**
     * Ban the user (current_group -> banned)
     *
     * @access public
     * @param $username
     * @param $id
     *
     */
    public function ban(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if (\App\Policy::isModerator($user) || auth()->user()->id == $user->id) {
            return redirect()->route('home')->with(Toastr::error('You Cannot Ban Yourself Or Other Staff!', 'Whoops!', ['options']));
        } else {
            $user->roles()->delete();
            $user->addRole('Banned');
            $user->setMainRole('Banned');

            $staff = auth()->user();
            $v = validator($request->all(), [
            'owned_by' => 'required',
            'created_by' => 'required|numeric',
            'ban_reason' => 'required',
            ]);

            $ban = new Ban();
            $ban->owned_by = $user->id;
            $ban->created_by = $staff->id;
            $ban->ban_reason = $request->input('ban_reason');
            $ban->save();

            // Activity Log
            \LogActivity::addToLog("Staff Member {$staff->username} has banned member {$user->username}.");

            // Send Email
            Mail::to($user->email)->send(new BanUser($user));

            return redirect()->route('home')->with(Toastr::success('User Is Now Banned!', 'Yay!', ['options']));
        }
    }


    /**
     * Unban the user (banned -> new group)
     *
     * @access public
     * @param $username
     * @param $id
     *
     */
    public function unban(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if (\App\Policy::isModerator($user) || auth()->user()->id == $user->id) {
            return redirect()->route('home')->with(Toastr::error('You Cannot Unban Yourself Or Other Staff!', 'Whoops!', ['options']));
        } else {
            $user->roles()->delete();
            $user->addRole($request->input('role'));
            $user->setMainRole($request->input('role'));

            $staff = auth()->user();
            $v = validator($request->all(), [
            'unban_reason' => 'required',
            'removed_at' => 'required'
            ]);

            $ban = new Ban();
            $ban->owned_by = $user->id;
            $ban->created_by = $staff->id;
            $ban->unban_reason = $request->input('unban_reason');
            $ban->removed_at = Carbon::now();
            $ban->save();

            // Activity Log
            \LogActivity::addToLog("Staff Member {$staff->username} has unbanned member {$user->username}.");

            // Send Email
            Mail::to($user->email)->send(new UnbanUser($user));

            return redirect()->route('home')->with(Toastr::success('User Is Now Relieved Of His Ban!', 'Yay!', ['options']));
        }
    }
}
