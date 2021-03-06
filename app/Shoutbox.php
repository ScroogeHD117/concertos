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

namespace App;

use Emojione\Client;
use Emojione\Ruleset;
use Illuminate\Database\Eloquent\Model;
use Decoda\Decoda;
use App\Helpers\Bbcode;


class Shoutbox extends Model
{

    protected $table = 'shoutbox';
    protected $fillable = ['user', 'message', 'mentions', 'messages'];

    /**
     * Get The Poster
     *
     * @access public
     * @return
     */
    public function poster()
    {
        return $this->belongsTo(\App\User::class, 'user');
    }

    /**
     * Parse content and return valid HTML
     *
     */
    public static function getMessageHtml($message)
    {
        return Bbcode::parse($message);
    }

    public function asHtml() {
        $client = new Client(new Ruleset());
        return $client->shortnameToUnicode(Shoutbox::getMessageHtml($this->message));
    }
}
