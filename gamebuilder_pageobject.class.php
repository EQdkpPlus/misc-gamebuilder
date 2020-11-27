<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2006
 * Date:		$Date: 2014-03-01 16:14:48 +0100 (Sa, 01 Mrz 2014) $
 * -----------------------------------------------------------------------
 * @author		$Author: godmod $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 14105 $
 *
 * $Id: wrapper_pageobject.class.php 14105 2014-03-01 15:14:48Z godmod $
 */

class gamebuilder_pageobject extends pageobject {

	private $data = false;

	public function __construct() {
		$handler = array(
		'load' => array('process' => 'load_userfile'),
		'check_gamename' => array('process' => 'ajax_checkgamename'),
		);
		parent::__construct(false, $handler, array());
		$this->process();
	}
	
	public function load_userfile(){
		
		if(!$this->user->is_signedin() || !$this->in->exists('load')) return false;
		
		$strUserFolder = $this->pfh->FolderPath('user_json/'.$this->user->id, "gamebuilder");
		$arrUserFiles = sdir($strUserFolder);
		$intKey = $this->in->get('load', 0);

		if(isset($arrUserFiles[$intKey])){
			$strFilename = $arrUserFiles[$intKey];

			$strContent = file_get_contents($strUserFolder.'/'.$strFilename);

			if(!json_decode($strContent)) return false;
			
			echo '<html>
			<body onload="document.transform.submit();">
			<form method="post" action="https://download.eqdkp-plus.eu/gamebuilder" name="transform" target="_parent">
			<input name="json" value="1" type="hidden" />
			<input name="json_plain" value="'.htmlspecialchars($strContent).'" type="hidden" />
			</form>
			</body>
			</html>';
			
			exit();
		}

	}
	
	public function ajax_checkgamename(){
		$strValue = $this->in->get('val');
		
		if(strlen($strValue) < 3) exit();
		$objQuery = $this->db->prepare("SELECT * FROM __repo_packages WHERE folderid LIKE ".$this->db->escapeString('%'.$strValue.'%')." AND category=7 AND status=2 ORDER BY dep_coreversion_to DESC LIMIT 3;")->execute();
		if($objQuery){
			$allGames = $objQuery->fetchAllAssoc();
			if(count($allGames)){
				echo "<div><h3>Did you mean one of the following games?</h3></div>";
				foreach($allGames as $game){
					$link = $this->routing->build("ExtensionList", $game['name'], 'p'.$game['id']);
					echo "<div style='float:left; width: 180px; margin-right: 10px; border-right: 1px solid #ddd;'><a href=\"".$link."\" target='_blank'><h3>".$game['name']."</h3><br />".$game['description']."</a></div>";
				}
				echo "<div class='clear'></div>";
			} else {
				$objQuery = $this->db->prepare("SELECT * FROM __repo_packages WHERE name LIKE ".$this->db->escapeString('%'.$strValue.'%')." AND category=7 AND status=2 ORDER BY dep_coreversion_to DESC LIMIT 3;")->execute();
				if($objQuery){
					$allGames = $objQuery->fetchAllAssoc();
					if(count($allGames)){
						echo "<div><h3>Did you mean one of the following games?</h3></div>";
						foreach($allGames as $game){
							$link = $this->routing->build("ExtensionList", $game['name'], 'p'.$game['id']);
							echo "<div style='float:left; width: 180px; margin-right: 10px; border-right: 1px solid #ddd;'><a href=\"".$link."\" target='_blank'><h3>".$game['name']."</h3><br />".$game['description']."</a></div>";
						}
						echo "<div class='clear'></div>";
					}
				}

			}
		}
		
		exit;
	}

	private function increase_val($a){
		foreach($a as $key => $val){
			$a[$key] = (int)$val+1;
		}
		return $a;
	}
	
	private function decrease_val($a){
		foreach($a as $key => $val){
			$a[$key] = (int)$val-1;
		}
		return $a;
	}
	
	private function increase_keyandval($a){
		$o = array();
		foreach($a as $key => $val){ 
			$o[$key+1] = (int)$val+1;
		}
		return $o;
	}
	
	private function increase_key($a){
		$o = array();
		foreach($a as $key => $val){ 
			$o[$key+1] = $val;
		}
		return $o;
	}
	public function sanitize($mssg, $rmlb=false){
		$mssg = html_entity_decode($mssg, ENT_COMPAT, 'UTF-8');
		$mssg = str_replace('&#039;', "'", $mssg);
		$mssg = str_replace('"', "'", $mssg);
		if($rmlb){
			$mssg = str_replace(array("\n", "\r"), '', $mssg);
		}
		return addslashes($mssg);
	}
	
	public function escapeDoubleQuotes($mssg){
		$mssg = str_replace('"', "'", $mssg);
		return $mssg;
	}
	
	public function display(){
		if($this->in->exists('json')){
			//Check if FIle Upload
			if(isset($_FILES['json_file']) && strlen($_FILES['json_file']['tmp_name'])){
				$json = trim(file_get_contents($_FILES['json_file']['tmp_name']));
			} else {
				$json = $this->in->get('json_plain', '', 'raw');
			}

			//$json = '{"icons":{"classes":{"classes":{"2":"data:image\/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8\/9hAAAABmJLR0QA\/wD\/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QEKCQQWr8AnXwAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAB3UlEQVQ4y42TMWhTQRjHfy+NbV1q+mgFoYOSQElqurlpeM\/JVYK0dNLNLhUn3fKyOQm66OBkQWkkq0uHpKlTxRRMSReDDilBbZsgFGL7fTmHFyIvTaoHf467+9\/v7r7vO6uW46xmur01zBDudCC2UDu18GUt6tlXHwBwuPPMiy3UvAEeQqqDyapk7LkV7LkVVMkM8XQBehRQdTXq2Ynl3thOLFNdjXr9PlWwjDFwvBkgV96kTHKp1D9HcqkUjMXoDcLbrywPgle8lFwEOQx4p+OLbL9Omb5XZK2tF5hr92vw+xu0v\/JfbfwKjF3m48soI\/duYdW3njsXIqOMnT8HnaN\/6ld9k\/LaXTodstbGUwAcoDDvpolcnDnz8NaPOp8LeQAXKIZEIPXQFEVwy+t5mnsVOPk+UM29CuX1PCK43T2ERHzyzdtuUQSMNIcCjDQR8b0AAcD7twVHBOyIwMn+QNkRQcT3sjvvA1SBygyqOLHZCT99XR00Ghw0GoG52OwEqjjIIaoQVgWSdTRnZezJY9AW+z+VD6U2QLEbO+d6apyp6RHsSfVLW1veX8AnC1WYstvs7kC1CoCbTvuAfB5no9AuJBIQj\/t\/oFfKuUdw54nh3eNeRWYBb0gWT3n+ADqoJeEFcDEQAAAAAElFTkSuQmCC"},"races":{"3":"data:image\/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAKOWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAEjHnZZ3VFTXFofPvXd6oc0wAlKG3rvAANJ7k15FYZgZYCgDDjM0sSGiAhFFRJoiSFDEgNFQJFZEsRAUVLAHJAgoMRhFVCxvRtaLrqy89\/Ly++Osb+2z97n77L3PWhcAkqcvl5cGSwGQyhPwgzyc6RGRUXTsAIABHmCAKQBMVka6X7B7CBDJy82FniFyAl8EAfB6WLwCcNPQM4BOB\/+fpFnpfIHomAARm7M5GSwRF4g4JUuQLrbPipgalyxmGCVmvihBEcuJOWGRDT77LLKjmNmpPLaIxTmns1PZYu4V8bZMIUfEiK+ICzO5nCwR3xKxRoowlSviN+LYVA4zAwAUSWwXcFiJIjYRMYkfEuQi4uUA4EgJX3HcVyzgZAvEl3JJS8\/hcxMSBXQdli7d1NqaQffkZKVwBALDACYrmcln013SUtOZvBwAFu\/8WTLi2tJFRbY0tba0NDQzMv2qUP91829K3NtFehn4uWcQrf+L7a\/80hoAYMyJarPziy2uCoDOLQDI3fti0zgAgKSobx3Xv7oPTTwviQJBuo2xcVZWlhGXwzISF\/QP\/U+Hv6GvvmckPu6P8tBdOfFMYYqALq4bKy0lTcinZ6QzWRy64Z+H+B8H\/nUeBkGceA6fwxNFhImmjMtLELWbx+YKuGk8Opf3n5r4D8P+pMW5FonS+BFQY4yA1HUqQH7tBygKESDR+8Vd\/6NvvvgwIH554SqTi3P\/7zf9Z8Gl4iWDm\/A5ziUohM4S8jMX98TPEqABAUgCKpAHykAd6ABDYAasgC1wBG7AG\/iDEBAJVgMWSASpgA+yQB7YBApBMdgJ9oBqUAcaQTNoBcdBJzgFzoNL4Bq4AW6D+2AUTIBnYBa8BgsQBGEhMkSB5CEVSBPSh8wgBmQPuUG+UBAUCcVCCRAPEkJ50GaoGCqDqqF6qBn6HjoJnYeuQIPQXWgMmoZ+h97BCEyCqbASrAUbwwzYCfaBQ+BVcAK8Bs6FC+AdcCXcAB+FO+Dz8DX4NjwKP4PnEIAQERqiihgiDMQF8UeikHiEj6xHipAKpAFpRbqRPuQmMorMIG9RGBQFRUcZomxRnqhQFAu1BrUeVYKqRh1GdaB6UTdRY6hZ1Ec0Ga2I1kfboL3QEegEdBa6EF2BbkK3oy+ib6Mn0K8xGAwNo42xwnhiIjFJmLWYEsw+TBvmHGYQM46Zw2Kx8lh9rB3WH8vECrCF2CrsUexZ7BB2AvsGR8Sp4Mxw7rgoHA+Xj6vAHcGdwQ3hJnELeCm8Jt4G749n43PwpfhGfDf+On4Cv0CQJmgT7AghhCTCJkIloZVwkfCA8JJIJKoRrYmBRC5xI7GSeIx4mThGfEuSIemRXEjRJCFpB+kQ6RzpLuklmUzWIjuSo8gC8g5yM\/kC+RH5jQRFwkjCS4ItsUGiRqJDYkjiuSReUlPSSXK1ZK5kheQJyeuSM1J4KS0pFymm1HqpGqmTUiNSc9IUaVNpf+lU6RLpI9JXpKdksDJaMm4ybJkCmYMyF2TGKQhFneJCYVE2UxopFykTVAxVm+pFTaIWU7+jDlBnZWVkl8mGyWbL1sielh2lITQtmhcthVZKO04bpr1borTEaQlnyfYlrUuGlszLLZVzlOPIFcm1yd2WeydPl3eTT5bfJd8p\/1ABpaCnEKiQpbBf4aLCzFLqUtulrKVFS48vvacIK+opBimuVTyo2K84p6Ss5KGUrlSldEFpRpmm7KicpFyufEZ5WoWiYq\/CVSlXOavylC5Ld6Kn0CvpvfRZVUVVT1Whar3qgOqCmrZaqFq+WpvaQ3WCOkM9Xr1cvUd9VkNFw08jT6NF454mXpOhmai5V7NPc15LWytca6tWp9aUtpy2l3audov2Ax2yjoPOGp0GnVu6GF2GbrLuPt0berCehV6iXo3edX1Y31Kfq79Pf9AAbWBtwDNoMBgxJBk6GWYathiOGdGMfI3yjTqNnhtrGEcZ7zLuM\/5oYmGSYtJoct9UxtTbNN+02\/R3Mz0zllmN2S1zsrm7+QbzLvMXy\/SXcZbtX3bHgmLhZ7HVosfig6WVJd+y1XLaSsMq1qrWaoRBZQQwShiXrdHWztYbrE9Zv7WxtBHYHLf5zdbQNtn2iO3Ucu3lnOWNy8ft1OyYdvV2o\/Z0+1j7A\/ajDqoOTIcGh8eO6o5sxybHSSddpySno07PnU2c+c7tzvMuNi7rXM65Iq4erkWuA24ybqFu1W6P3NXcE9xb3Gc9LDzWepzzRHv6eO7yHPFS8mJ5NXvNelt5r\/Pu9SH5BPtU+zz21fPl+3b7wX7efrv9HqzQXMFb0ekP\/L38d\/s\/DNAOWBPwYyAmMCCwJvBJkGlQXlBfMCU4JvhI8OsQ55DSkPuhOqHC0J4wybDosOaw+XDX8LLw0QjjiHUR1yIVIrmRXVHYqLCopqi5lW4r96yciLaILoweXqW9KnvVldUKq1NWn46RjGHGnIhFx4bHHol9z\/RnNjDn4rziauNmWS6svaxnbEd2OXuaY8cp40zG28WXxU8l2CXsTphOdEisSJzhunCruS+SPJPqkuaT\/ZMPJX9KCU9pS8Wlxqae5Mnwknm9acpp2WmD6frphemja2zW7Fkzy\/fhN2VAGasyugRU0c9Uv1BHuEU4lmmfWZP5Jiss60S2dDYvuz9HL2d7zmSue+63a1FrWWt78lTzNuWNrXNaV78eWh+3vmeD+oaCDRMbPTYe3kTYlLzpp3yT\/LL8V5vDN3cXKBVsLBjf4rGlpVCikF84stV2a9021DbutoHt5turtn8sYhddLTYprih+X8IqufqN6TeV33zaEb9joNSydP9OzE7ezuFdDrsOl0mX5ZaN7\/bb3VFOLy8qf7UnZs+VimUVdXsJe4V7Ryt9K7uqNKp2Vr2vTqy+XeNc01arWLu9dn4fe9\/Qfsf9rXVKdcV17w5wD9yp96jvaNBqqDiIOZh58EljWGPft4xvm5sUmoqbPhziHRo9HHS4t9mqufmI4pHSFrhF2DJ9NProje9cv+tqNWytb6O1FR8Dx4THnn4f+\/3wcZ\/jPScYJ1p\/0Pyhtp3SXtQBdeR0zHYmdo52RXYNnvQ+2dNt293+o9GPh06pnqo5LXu69AzhTMGZT2dzz86dSz83cz7h\/HhPTM\/9CxEXbvUG9g5c9Ll4+ZL7pQt9Tn1nL9tdPnXF5srJq4yrndcsr3X0W\/S3\/2TxU\/uA5UDHdavrXTesb3QPLh88M+QwdP6m681Lt7xuXbu94vbgcOjwnZHokdE77DtTd1PuvriXeW\/h\/sYH6AdFD6UeVjxSfNTws+7PbaOWo6fHXMf6Hwc\/vj\/OGn\/2S8Yv7ycKnpCfVEyqTDZPmU2dmnafvvF05dOJZ+nPFmYKf5X+tfa5zvMffnP8rX82YnbiBf\/Fp99LXsq\/PPRq2aueuYC5R69TXy\/MF72Rf3P4LeNt37vwd5MLWe+x7ys\/6H7o\/ujz8cGn1E+f\/gUDmPP8usTo0wAAAAZiS0dEAP8A\/wD\/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90BCgkFDTy+3\/IAAAVESURBVDjLpZRrTJN3FMbP\/317v89yKS2soIIUEAtjk1ilyjQgwxtZXaaibHOaOLehmxE1usTbdNGp02RxLF6jCzFTJyxgHEhloltULkWQci3ltfQCLRRa3tK+\/31YlkzjnG7Pt+ck55fnwzkPCc8RnwDOsnmvSRUSLi8iNnHXDK12sKPDPAAvINbTA4FAgHw+X2SiAMTLli\/6hkcEp3plLOygydhBerQDABr\/Ezg+Ph5ptWkno+SSNwQ+SsENjGGehA+SwASiOgfUAAAnLl2EjYYVzwWTTw+GnE4sYBPc8CjVKr9vFPMDbrB5aZy8uAiv27Z3bnTClN82r\/mg86UTr9m6BSbJxK8GHvdgqVwGyrR8vGHFGsJP+\/AIE0IhAW8TAFQlaRKgtc0M+Tl61Ndvw80Pzc8GH9hZDPdN7bBp\/wHUXG\/cmpJeDJN5QiAAiOrbt\/AQg4Ee9QEz6s8GANTaZsZL8nPloQl6MZ\/POwsAzBPkRTlzNZ9\/VLT0L7+60JB5q\/MRbh8dwe5ggAlhjA9eOM0cvWUM8ITC0JFzZzEAZL6\/suBt9auqXSpVtBoAIBQKPZm4+WGHhUHs\/CWL8vYReCIuRI\/bOAGmTM7lLh\/HDBIDQKRCAZ7H\/eS7e3ejFlMTAMBdPx2sSEnSUKEJeu6bs9PPkyTJAACcOXMmtrW1VcOy9FM+kiTPiSXi4zNmTHcH\/V4FZaOupU6bZhjBQeihx1BuVjbYA36UT5IgJDngHXLeZFyuK1ERYanGutuXq6rrGJ1Ot9BgMGzW6XR6kiQ\/RdlzZmoDQWZpWITS1dDQEJehTaxBImnCsZOlBzstvSwhmwOU0wm6zJnwoK0NoqOVIBCJx1cuzDNkTI5hWi3O1jm6WSdFIlFOVlYW2Gy2642NjQYWh8UyT9UkxyEAlljArq+8YfwlKzMtocp480TeHP2mz34qD1EDdqgZckOJXk+GS2TQYLeSjvbmtAHZK551H679vsVkUjY1NTFms7mMoqgt5eXlXpZj2M\/J0Wj0XV1d5b3d3TW+AKaDmGWq\/eGSJhIRlGvKFMWdwtXEg8pKVGF4pyuWyx6UyCeFpcaqClUxcQlXL1\/uaTebt9+7d+8UANgBAPR6PbBkMplYJpNx1Wp1QZhSvZs9PCxbv7H4aEx0dLLD4cBbhHxs3VYCvWM+WJaeHjEemogBqZjjDvqaW4xXa39v6V7lCQD194swGo2AUlNTpasLC++oY2M1VVVVV7RabTo9NqaquVnjLyx6j3+jppqcPl+PhSM+JBFJB0tLS2UWi2Wks6szBQBsAACzElVQ\/4h68qXtdjstFImS5XL56xw2W+N0uyXDPR1YwOagBG06K0apJAg6iLgsdkdZWVmOd9SbJZVKwwmCuOTxeAYAAKwu77O7wmKxGAUCwYIB24DKS\/uZlUe+IsUCEStOEYV4fD6ODA9HJpPpRFtbW0VBQcH6vr6+cB6P+2Nfn7X3n7qCAABgk2SIpmlOP9UP93+tRxdLvgAJIpGVooDP5xMMw4BYLBbX1taOu1yuHbm5uYccDuf9fy0htTZVzCDEYxDAgrcW4nmzs4JWa\/+Ix+ORWiwWdkpKCrZa\/0y3b9++awBw7YXabX5eztrpGRnTwpQKGA7Q5PnDx0oqLpR9\/cnOHdcjwyZlezyeUICmm+ElRAIAYITu1v1cdXdqkib6scVa+d2eAzsAgPvlhdO7fUxI2u+0u+tuVJ+y9PbaXhSMnuExxhgQQrD92KH9YQpFkUwqAaqze8\/OjcXfwv\/V9uOHAQBg1ccbouJTkiI5fD73Zfb\/AASWTwFaASfwAAAAAElFTkSuQmCC"}},"roles":{"1":"data:image\/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8\/9hAAAABmJLR0QA\/wD\/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QEKCQQWr8AnXwAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAB3UlEQVQ4y42TMWhTQRjHfy+NbV1q+mgFoYOSQElqurlpeM\/JVYK0dNLNLhUn3fKyOQm66OBkQWkkq0uHpKlTxRRMSReDDilBbZsgFGL7fTmHFyIvTaoHf467+9\/v7r7vO6uW46xmur01zBDudCC2UDu18GUt6tlXHwBwuPPMiy3UvAEeQqqDyapk7LkV7LkVVMkM8XQBehRQdTXq2Ynl3thOLFNdjXr9PlWwjDFwvBkgV96kTHKp1D9HcqkUjMXoDcLbrywPgle8lFwEOQx4p+OLbL9Omb5XZK2tF5hr92vw+xu0v\/JfbfwKjF3m48soI\/duYdW3njsXIqOMnT8HnaN\/6ld9k\/LaXTodstbGUwAcoDDvpolcnDnz8NaPOp8LeQAXKIZEIPXQFEVwy+t5mnsVOPk+UM29CuX1PCK43T2ERHzyzdtuUQSMNIcCjDQR8b0AAcD7twVHBOyIwMn+QNkRQcT3sjvvA1SBygyqOLHZCT99XR00Ghw0GoG52OwEqjjIIaoQVgWSdTRnZezJY9AW+z+VD6U2QLEbO+d6apyp6RHsSfVLW1veX8AnC1WYstvs7kC1CoCbTvuAfB5no9AuJBIQj\/t\/oFfKuUdw54nh3eNeRWYBb0gWT3n+ADqoJeEFcDEQAAAAAElFTkSuQmCC","2":"data:image\/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAKOWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAEjHnZZ3VFTXFofPvXd6oc0wAlKG3rvAANJ7k15FYZgZYCgDDjM0sSGiAhFFRJoiSFDEgNFQJFZEsRAUVLAHJAgoMRhFVCxvRtaLrqy89\/Ly++Osb+2z97n77L3PWhcAkqcvl5cGSwGQyhPwgzyc6RGRUXTsAIABHmCAKQBMVka6X7B7CBDJy82FniFyAl8EAfB6WLwCcNPQM4BOB\/+fpFnpfIHomAARm7M5GSwRF4g4JUuQLrbPipgalyxmGCVmvihBEcuJOWGRDT77LLKjmNmpPLaIxTmns1PZYu4V8bZMIUfEiK+ICzO5nCwR3xKxRoowlSviN+LYVA4zAwAUSWwXcFiJIjYRMYkfEuQi4uUA4EgJX3HcVyzgZAvEl3JJS8\/hcxMSBXQdli7d1NqaQffkZKVwBALDACYrmcln013SUtOZvBwAFu\/8WTLi2tJFRbY0tba0NDQzMv2qUP91829K3NtFehn4uWcQrf+L7a\/80hoAYMyJarPziy2uCoDOLQDI3fti0zgAgKSobx3Xv7oPTTwviQJBuo2xcVZWlhGXwzISF\/QP\/U+Hv6GvvmckPu6P8tBdOfFMYYqALq4bKy0lTcinZ6QzWRy64Z+H+B8H\/nUeBkGceA6fwxNFhImmjMtLELWbx+YKuGk8Opf3n5r4D8P+pMW5FonS+BFQY4yA1HUqQH7tBygKESDR+8Vd\/6NvvvgwIH554SqTi3P\/7zf9Z8Gl4iWDm\/A5ziUohM4S8jMX98TPEqABAUgCKpAHykAd6ABDYAasgC1wBG7AG\/iDEBAJVgMWSASpgA+yQB7YBApBMdgJ9oBqUAcaQTNoBcdBJzgFzoNL4Bq4AW6D+2AUTIBnYBa8BgsQBGEhMkSB5CEVSBPSh8wgBmQPuUG+UBAUCcVCCRAPEkJ50GaoGCqDqqF6qBn6HjoJnYeuQIPQXWgMmoZ+h97BCEyCqbASrAUbwwzYCfaBQ+BVcAK8Bs6FC+AdcCXcAB+FO+Dz8DX4NjwKP4PnEIAQERqiihgiDMQF8UeikHiEj6xHipAKpAFpRbqRPuQmMorMIG9RGBQFRUcZomxRnqhQFAu1BrUeVYKqRh1GdaB6UTdRY6hZ1Ec0Ga2I1kfboL3QEegEdBa6EF2BbkK3oy+ib6Mn0K8xGAwNo42xwnhiIjFJmLWYEsw+TBvmHGYQM46Zw2Kx8lh9rB3WH8vECrCF2CrsUexZ7BB2AvsGR8Sp4Mxw7rgoHA+Xj6vAHcGdwQ3hJnELeCm8Jt4G749n43PwpfhGfDf+On4Cv0CQJmgT7AghhCTCJkIloZVwkfCA8JJIJKoRrYmBRC5xI7GSeIx4mThGfEuSIemRXEjRJCFpB+kQ6RzpLuklmUzWIjuSo8gC8g5yM\/kC+RH5jQRFwkjCS4ItsUGiRqJDYkjiuSReUlPSSXK1ZK5kheQJyeuSM1J4KS0pFymm1HqpGqmTUiNSc9IUaVNpf+lU6RLpI9JXpKdksDJaMm4ybJkCmYMyF2TGKQhFneJCYVE2UxopFykTVAxVm+pFTaIWU7+jDlBnZWVkl8mGyWbL1sielh2lITQtmhcthVZKO04bpr1borTEaQlnyfYlrUuGlszLLZVzlOPIFcm1yd2WeydPl3eTT5bfJd8p\/1ABpaCnEKiQpbBf4aLCzFLqUtulrKVFS48vvacIK+opBimuVTyo2K84p6Ss5KGUrlSldEFpRpmm7KicpFyufEZ5WoWiYq\/CVSlXOavylC5Ld6Kn0CvpvfRZVUVVT1Whar3qgOqCmrZaqFq+WpvaQ3WCOkM9Xr1cvUd9VkNFw08jT6NF454mXpOhmai5V7NPc15LWytca6tWp9aUtpy2l3audov2Ax2yjoPOGp0GnVu6GF2GbrLuPt0berCehV6iXo3edX1Y31Kfq79Pf9AAbWBtwDNoMBgxJBk6GWYathiOGdGMfI3yjTqNnhtrGEcZ7zLuM\/5oYmGSYtJoct9UxtTbNN+02\/R3Mz0zllmN2S1zsrm7+QbzLvMXy\/SXcZbtX3bHgmLhZ7HVosfig6WVJd+y1XLaSsMq1qrWaoRBZQQwShiXrdHWztYbrE9Zv7WxtBHYHLf5zdbQNtn2iO3Ucu3lnOWNy8ft1OyYdvV2o\/Z0+1j7A\/ajDqoOTIcGh8eO6o5sxybHSSddpySno07PnU2c+c7tzvMuNi7rXM65Iq4erkWuA24ybqFu1W6P3NXcE9xb3Gc9LDzWepzzRHv6eO7yHPFS8mJ5NXvNelt5r\/Pu9SH5BPtU+zz21fPl+3b7wX7efrv9HqzQXMFb0ekP\/L38d\/s\/DNAOWBPwYyAmMCCwJvBJkGlQXlBfMCU4JvhI8OsQ55DSkPuhOqHC0J4wybDosOaw+XDX8LLw0QjjiHUR1yIVIrmRXVHYqLCopqi5lW4r96yciLaILoweXqW9KnvVldUKq1NWn46RjGHGnIhFx4bHHol9z\/RnNjDn4rziauNmWS6svaxnbEd2OXuaY8cp40zG28WXxU8l2CXsTphOdEisSJzhunCruS+SPJPqkuaT\/ZMPJX9KCU9pS8Wlxqae5Mnwknm9acpp2WmD6frphemja2zW7Fkzy\/fhN2VAGasyugRU0c9Uv1BHuEU4lmmfWZP5Jiss60S2dDYvuz9HL2d7zmSue+63a1FrWWt78lTzNuWNrXNaV78eWh+3vmeD+oaCDRMbPTYe3kTYlLzpp3yT\/LL8V5vDN3cXKBVsLBjf4rGlpVCikF84stV2a9021DbutoHt5turtn8sYhddLTYprih+X8IqufqN6TeV33zaEb9joNSydP9OzE7ezuFdDrsOl0mX5ZaN7\/bb3VFOLy8qf7UnZs+VimUVdXsJe4V7Ryt9K7uqNKp2Vr2vTqy+XeNc01arWLu9dn4fe9\/Qfsf9rXVKdcV17w5wD9yp96jvaNBqqDiIOZh58EljWGPft4xvm5sUmoqbPhziHRo9HHS4t9mqufmI4pHSFrhF2DJ9NProje9cv+tqNWytb6O1FR8Dx4THnn4f+\/3wcZ\/jPScYJ1p\/0Pyhtp3SXtQBdeR0zHYmdo52RXYNnvQ+2dNt293+o9GPh06pnqo5LXu69AzhTMGZT2dzz86dSz83cz7h\/HhPTM\/9CxEXbvUG9g5c9Ll4+ZL7pQt9Tn1nL9tdPnXF5srJq4yrndcsr3X0W\/S3\/2TxU\/uA5UDHdavrXTesb3QPLh88M+QwdP6m681Lt7xuXbu94vbgcOjwnZHokdE77DtTd1PuvriXeW\/h\/sYH6AdFD6UeVjxSfNTws+7PbaOWo6fHXMf6Hwc\/vj\/OGn\/2S8Yv7ycKnpCfVEyqTDZPmU2dmnafvvF05dOJZ+nPFmYKf5X+tfa5zvMffnP8rX82YnbiBf\/Fp99LXsq\/PPRq2aueuYC5R69TXy\/MF72Rf3P4LeNt37vwd5MLWe+x7ys\/6H7o\/ujz8cGn1E+f\/gUDmPP8usTo0wAAAAZiS0dEAP8A\/wD\/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90BCgkFDTy+3\/IAAAVESURBVDjLpZRrTJN3FMbP\/317v89yKS2soIIUEAtjk1ilyjQgwxtZXaaibHOaOLehmxE1usTbdNGp02RxLF6jCzFTJyxgHEhloltULkWQci3ltfQCLRRa3tK+\/31YlkzjnG7Pt+ck55fnwzkPCc8RnwDOsnmvSRUSLi8iNnHXDK12sKPDPAAvINbTA4FAgHw+X2SiAMTLli\/6hkcEp3plLOygydhBerQDABr\/Ezg+Ph5ptWkno+SSNwQ+SsENjGGehA+SwASiOgfUAAAnLl2EjYYVzwWTTw+GnE4sYBPc8CjVKr9vFPMDbrB5aZy8uAiv27Z3bnTClN82r\/mg86UTr9m6BSbJxK8GHvdgqVwGyrR8vGHFGsJP+\/AIE0IhAW8TAFQlaRKgtc0M+Tl61Ndvw80Pzc8GH9hZDPdN7bBp\/wHUXG\/cmpJeDJN5QiAAiOrbt\/AQg4Ee9QEz6s8GANTaZsZL8nPloQl6MZ\/POwsAzBPkRTlzNZ9\/VLT0L7+60JB5q\/MRbh8dwe5ggAlhjA9eOM0cvWUM8ITC0JFzZzEAZL6\/suBt9auqXSpVtBoAIBQKPZm4+WGHhUHs\/CWL8vYReCIuRI\/bOAGmTM7lLh\/HDBIDQKRCAZ7H\/eS7e3ejFlMTAMBdPx2sSEnSUKEJeu6bs9PPkyTJAACcOXMmtrW1VcOy9FM+kiTPiSXi4zNmTHcH\/V4FZaOupU6bZhjBQeihx1BuVjbYA36UT5IgJDngHXLeZFyuK1ERYanGutuXq6rrGJ1Ot9BgMGzW6XR6kiQ\/RdlzZmoDQWZpWITS1dDQEJehTaxBImnCsZOlBzstvSwhmwOU0wm6zJnwoK0NoqOVIBCJx1cuzDNkTI5hWi3O1jm6WSdFIlFOVlYW2Gy2642NjQYWh8UyT9UkxyEAlljArq+8YfwlKzMtocp480TeHP2mz34qD1EDdqgZckOJXk+GS2TQYLeSjvbmtAHZK551H679vsVkUjY1NTFms7mMoqgt5eXlXpZj2M\/J0Wj0XV1d5b3d3TW+AKaDmGWq\/eGSJhIRlGvKFMWdwtXEg8pKVGF4pyuWyx6UyCeFpcaqClUxcQlXL1\/uaTebt9+7d+8UANgBAPR6PbBkMplYJpNx1Wp1QZhSvZs9PCxbv7H4aEx0dLLD4cBbhHxs3VYCvWM+WJaeHjEemogBqZjjDvqaW4xXa39v6V7lCQD194swGo2AUlNTpasLC++oY2M1VVVVV7RabTo9NqaquVnjLyx6j3+jppqcPl+PhSM+JBFJB0tLS2UWi2Wks6szBQBsAACzElVQ\/4h68qXtdjstFImS5XL56xw2W+N0uyXDPR1YwOagBG06K0apJAg6iLgsdkdZWVmOd9SbJZVKwwmCuOTxeAYAAKwu77O7wmKxGAUCwYIB24DKS\/uZlUe+IsUCEStOEYV4fD6ODA9HJpPpRFtbW0VBQcH6vr6+cB6P+2Nfn7X3n7qCAABgk2SIpmlOP9UP93+tRxdLvgAJIpGVooDP5xMMw4BYLBbX1taOu1yuHbm5uYccDuf9fy0htTZVzCDEYxDAgrcW4nmzs4JWa\/+Ix+ORWiwWdkpKCrZa\/0y3b9++awBw7YXabX5eztrpGRnTwpQKGA7Q5PnDx0oqLpR9\/cnOHdcjwyZlezyeUICmm+ElRAIAYITu1v1cdXdqkib6scVa+d2eAzsAgPvlhdO7fUxI2u+0u+tuVJ+y9PbaXhSMnuExxhgQQrD92KH9YQpFkUwqAaqze8\/OjcXfwv\/V9uOHAQBg1ccbouJTkiI5fD73Zfb\/AASWTwFaASfwAAAAAElFTkSuQmCC"}},"common":{"v":"0.1.0","g":"example","l":"english"},"classes":[{"name":"class","type":"classes","primary":true,"admin":false,"decorate":true,"colorize":true,"roster":true,"recruitment":true,"parent":false},{"name":"race","type":"races","primary":false,"admin":false,"decorate":true,"colorize":false,"roster":false,"recruitment":false,"parent":{"class":{"1":[1],"2":[1,2],"3":[2,3],"4":[3,4],"5":[4]}}}],"classcolors":{"1":"#ABD473","2":"#69CCF0","3":"#F58CBA","4":"#FFFFFF","5":"#0070DE"},"roles":{"1":[3,4],"2":[3],"3":[1,2,4,5],"4":[1,3]},"lang":{"classes":{"1":"Hunter","2":"Mage","3":"Paladin","4":"Priest","5":"Shaman"},"races":{"1":"Human","2":"Goblin","3":"Gnome","4":"Troll"},"roles":{"1":"Healer","2":"Tank","3":"Range-DD","4":"Melee"}},"sig":"d8dd3b8fb94c10318c9240c82367b254"}';
			$arrMyJSON = json_decode($json, true);
			
			if(!$arrMyJSON || $json == "" || $this->in->get('username') != ""){
				message_die("Invalid GAMEBUILDER.json File");
			}
						
			//General
			$this->tpl->add_js("
				function onlyUnique(value, index, self) { 
					return self.indexOf(value) === index;
				}

				$('input[name=\"name\"]').val(\"".$this->sanitize($arrMyJSON['common']['g'])."\");
				$('input[name=\"version\"]').val(\"".$this->sanitize($arrMyJSON['common']['v'])."\");
				$('input[name=\"lang\"]').val(\"".$this->sanitize($arrMyJSON['common']['l'])."\");
			", 'docready');
		
			
			//Classes
			$arrNameMapping = $arrTypeIDMapping = array();
			$intPrimary = false;
			
			$lfdClassID = 0;
			foreach($arrMyJSON['classes'] as $key => $val){
				$arrNameMapping[$val['name']] = $key+1;
				
				$mykey = $key+1;
				$this->tpl->add_js("
					addClassType();
					updateParentGeneralDD();
					$('input[name=\"classtype[".$mykey."][name]\"]').val(\"".$this->sanitize($val['name'])."\");
					$('input[name=\"classtype[".$mykey."][type]\"]').val(\"".$this->sanitize($val['type'])."\");
					$('input[name=\"classtype[".$mykey."][primary]\"]').prop('checked', ".(($val['primary']) ? 'true' : 'false').");
					$('input[name=\"classtype[".$mykey."][admin]\"]').prop('checked', ".(($val['admin']) ? 'true' : 'false').");
					$('input[name=\"classtype[".$mykey."][decorate]\"]').prop('checked', ".(($val['decorate']) ? 'true' : 'false').");
					$('input[name=\"classtype[".$mykey."][roster]\"]').prop('checked', ".(($val['roster']) ? 'true' : 'false').");
					$('input[name=\"classtype[".$mykey."][recruitment]\"]').prop('checked', ".(($val['recruitment']) ? 'true' : 'false').");
				", 'docready');

				$type = $val['type'];
				$arrClasses = $arrMyJSON['lang'][$type];
				$arrTypeIDMapping[$mykey] =  $arrClasses;
				foreach($arrClasses as $k => $classname){
					$this->tpl->add_js("
					addClass($('#classtype_".$mykey."').find('.add_class_button'));
					$('input[name=\"classtype[".$mykey."][class][".$lfdClassID."][name]\"]').val(\"".$this->sanitize($classname)."\");
					
				", "docready");
					if(isset($arrMyJSON['icons']['classes'][$type][$k])){
						$this->tpl->add_js("
						$('input[name=\"classtype[".$mykey."][class][".$lfdClassID."][file]\"]').parent().find('.uploadPreview').attr('src', \"".$this->sanitize($arrMyJSON['icons']['classes'][$type][$k])."\");
						$('input[name=\"classtype[".$mykey."][class][".$lfdClassID."][file]\"]').parent().find('.classtype-icon-base64').val(\"".$this->sanitize($arrMyJSON['icons']['classes'][$type][$k])."\");
						", 'docready');
					}
				
					if((int)$val['primary']){
						$strClasscolor = $arrMyJSON['classcolors'][$k];
						if($strClasscolor) {
							$this->tpl->add_js("
								$('input[name=\"classtype[".$mykey."][class][".$lfdClassID."][classcolor]\"]').val(\"".$this->sanitize(strtoupper($strClasscolor))."\");
							", "docready");					
						}
					}
				
				
					$lfdClassID++;
				}
			}
			
			//Set the parents
			foreach($arrMyJSON['classes'] as $key => $val){
				$mykey = $key+1;
				
					if($val['parent'] && is_array($val['parent'])){
						$arrParents = array_keys($val['parent']);
						
						$arrIDs = array();
						foreach($arrParents as $name){
							$arrIDs[] = $arrNameMapping[$name];
						}

						$this->tpl->add_js("
						$('#ms_parent_".$mykey."').val(".json_encode($arrIDs).").trigger('change');
						$('#ms_parent_".$mykey."').multiselect('destroy');
						$('#ms_parent_".$mykey."').multiselect().trigger('change');
						", 'docready');
					}
			}
			
			//Now the single classes
			$i = 0;
			foreach($arrMyJSON['classes'] as $key => $val){
				$type = $val['type'];
				$mykey = $key +1;
				
				$arrParents = $val['parent'];

				$arrClasses = $arrMyJSON['lang'][$type];
				
				$this->tpl->add_js("
							var class5array = [];
							var parentCollection = $('#classtype_".$mykey."').find('.classtype-parents').each( function(){
								var mytempclass = $(this).attr('class');								
								var array5 = mytempclass.split(\" \");
								var class5 = array5[1];
								class5array.push(class5);
							});
							
						", "docready");
				
				foreach($arrClasses as $k => $classname){
					
					
					if($arrParents && count($arrParents)){
						
						
						foreach($arrParents as $type => $vals){
							$intTypeID = $arrNameMapping[$type];
							
							$arrDeps = array();


							foreach($vals as $otherClassID => $arrMyIDs){
								if(in_array($k, $arrMyIDs)) $arrDeps[] = $otherClassID;
							}
							
							$this->tpl->add_js("
								$('select[name=\"classtype[".$mykey."][parent][".$i."][".$intTypeID."][]\"]').val(".json_encode($this->decrease_val($arrDeps)).");					
							", "docready");
							
						}
						
					}
					
					$i++;
				}
				
				$this->tpl->add_js("if(class5array.length > 0){
							var unique = class5array.filter( onlyUnique );
					
							for (i = 0; i < unique.length; i++) {
								class5 = unique[i];
								$('.'+class5).multiselect();
								$('.'+class5).multiselect(\"destroy\");
								$('.'+class5).multiselect();
							
							}
						}
						", "docready");
			}	
			
			//Roles
			foreach($arrMyJSON['roles'] as $key => $val){
				$a = $this->decrease_val($val);
				$this->tpl->add_js("
				addRole();		
				$('input[name=\"role[name][".($key-1)."]\"]').val(\"".$this->sanitize($arrMyJSON['lang']['roles'][$key])."\");
				$('select[name=\"role[classes][".($key-1)."][]\"]').val(".json_encode($a).");", 'docready');
				if($arrMyJSON['icons']['roles'][$key]){
					$this->tpl->add_js("
					$('input[name=\"role[file][".($key-1)."]\"]').parent().find('.uploadPreview').attr('src', \"".$this->sanitize($arrMyJSON['icons']['roles'][$key])."\");
					$('input[name=\"role[file][".($key-1)."]\"]').parent().find('.classtype-icon-base64').val(\"".$this->sanitize($arrMyJSON['icons']['roles'][$key])."\");
					", 'docready');
				}
			}
			
			//Events
			foreach($arrMyJSON['events'] as $key => $val){

				$this->tpl->add_js("
				addEvent();		
				$('input[name=\"events[name][".($key-1)."]\"]').val(\"".$this->sanitize($val['name'])."\");
				", 'docready');
				if($arrMyJSON['icons']['events'][$key]){
					$this->tpl->add_js("
					$('input[name=\"events[file][".($key-1)."]\"]').parent().find('.uploadPreview').attr('src', \"".$this->sanitize($arrMyJSON['icons']['events'][$key])."\");
					$('input[name=\"events[file][".($key-1)."]\"]').parent().find('.classtype-icon-base64').val(\"".$this->sanitize($arrMyJSON['icons']['events'][$key])."\");
					", 'docready');
				}
			}
			
			//Profilefields
			$intFields = 0;
			foreach($arrMyJSON['profilefields'] as $key => $val){
				$strOptions = implode("|", $val['options']);
				
				$this->tpl->add_js("
				addProfilefield();
				var diva = \"".$this->sanitize($strOptions)."\";
				$('input[name=\"profilefield[name][".($intFields)."]\"]').val(\"".$this->sanitize($arrMyJSON['lang']['lang']['uc_'.$key])."\");
				$('select[name=\"profilefield[type][".($intFields)."]\"]').val(\"".$val['type']."\");
				$('textarea[name=\"profilefield[options][".($intFields)."]\"]').val(diva.split('|').join('\\n'));
				$('select[name=\"profilefield[type][".($intFields)."]\"]').trigger('change');
				", 'docready');
				$intFields++;
			}	
			$this->tpl->add_js("
				$('.role-classes').multiselect();
				$('.role-classes').multiselect(\"destroy\");
				$('.role-classes').multiselect();
				
				$('#optionContainer').hide(); $('#gamebuilderContainer').show();
				
				$(\".mycolorpicker\").spectrum(\"destroy\");
				$(\".mycolorpicker\").spectrum({showInput: true, preferredFormat: \"hex6\", allowEmpty:true});
				
				cleanup_json();
			", "docready");

			$this->tpl->assign_vars(array(
				'S_FROM_JSON' => true,
			));
		
		}
		
		//===========================================================================================================
		// BUILD
		
		if ($this->in->exists('build')){
			$game = preg_replace("/[^a-zA-Z0-9_]/","",utf8_strtolower($this->in->get('name')));
			//$game = "wow";
			$version = preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($this->in->get('version')));
			//Create Game Class
			$filename = $game.'.class.php';
			$arrLang = array();
			
			$tmpFolder = md5(rand().rand()).'_tmp';

			$a = $_POST;
			
			//Build Name Array
			$arrClassNames = array();
			$arrClassTypes = array();
			foreach($a['classtype'] as $key => $value){
				if ($key == "CLASSID" || $value['name'] == "") continue;
				$arrClassNames[$key] = preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['name']));			
				$arrClassTypes[$key] = preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['type']));
			}
			$arrClassTypes = array_unique($arrClassTypes);
			
			//Get ClassTypes
			$arrClasses = array();
			$arrClassArray = array();
			$intPrimary = false;
			$arrClassIcons = array();
			$arrJSONData = array();
			
			foreach($a['classtype'] as $key => $value){
				if ($key == "CLASSID" || $value['name'] == "") continue;
				
				$value['type'] = preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['type']));
				$value['name'] = preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['name']));
				
				//Classes
				$classes = array();
				$correcturval = false;
				$i=1;
				$arrTmpBaseIcons = array();
				foreach($value['class'] as $key2 => $val2){	
					if ($key2 === "CURRENTID" || $val2['name'] === "") continue;
					if ($correcturval === false) {
						$correcturval = (int)$key2;
					} elseif((int)$key2 < $correcturval) $correcturval = (int)$key2;
					$arrLang[$value['type']][$i] = $val2['name'];
					$arrClassArray[$key][$i] = $val2;
										
					if (isset($_FILES['classtype']['tmp_name'][$key]['class'][$key2]['file']) && strlen($_FILES['classtype']['tmp_name'][$key]['class'][$key2]['file'])){
						$strName = $_FILES['classtype']['name'][$key]['class'][$key2]['file'];
						$ext = strtolower(pathinfo($strName, PATHINFO_EXTENSION));
						if(!in_array($ext, array('jpg', 'png'))) continue;
						
						$data = file_get_contents($_FILES['classtype']['tmp_name'][$key]['class'][$key2]['file']);
						$base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
						$arrTmpBaseIcons[$i] = $base64;
						
						$arrIcons[$i] = array(
							'name' => $_FILES['classtype']['name'][$key]['class'][$key2]['file'],
							'tmp_name' => $_FILES['classtype']['tmp_name'][$key]['class'][$key2]['file'],
						);
					}elseif($a['classtype'][$key]['class'][$key2]['file_base64']){
						$arrTmpBaseIcons[$i] = $a['classtype'][$key]['class'][$key2]['file_base64'];
							$splited = explode(',', substr( $a['classtype'][$key]['class'][$key2]['file_base64'] , 5 ) , 2);
							$mime=$splited[0];
							$data=$splited[1];

							$mime_split_without_base64=explode(';', $mime,2);
							$mime_split=explode('/', $mime_split_without_base64[0],2);
							if(count($mime_split)==2)
							{
								$extension=$mime_split[1];
								if($extension=='jpeg')$extension='jpg';
							}
							
							$output_folder = $this->pfh->FolderPath($tmpFolder, "gamebuilder");
							$strFilename = md5(rand()).'.'.$extension;
							$this->pfh->putContent($output_folder.'/'.$strFilename, base64_decode($data));
							$arrIcons[$i] = array(
								'name' 		=> $strFilename,
								'tmp_name'	=> $this->pfh->FilePath($output_folder.'/'.$strFilename),
								'is_local'	=> true,
							);
					}
					$i++;
				}
				
				//Primary
				if ((int)$value['primary']){
					$intPrimary = intval($value['primary']);
					$primary = true;
				} else $primary = false;
				
				//Build Parent Row
				$arrParent = array();
				foreach($value['parent'] as $classid => $parent){
					foreach($parent as $parentid => $parentoptions){
						foreach ($parentoptions as $parentoption){
							/*
							if ($parentoption == 'all') {
								$arrParent[$arrClassNames[$parentid]][$parentoption+1] = 'all';
								break;
							}
							*/
							$arrParent[$arrClassNames[$parentid]][$parentoption+1][] = ($classid-$correcturval)+1;
						}
					}
				}
				
				//Parent
				if (!isset($value['parents'])){
					$parent = false;
				} else {
					$parent = $arrParent;
				}
				
				$arrClasses[] = array(
					'name' => preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['name'])),
					'type' => preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['type'])),
					'primary' => $primary,
					'admin' => (int)$value['admin'] ? true : false,
					'decorate' => (int)$value['decorate'] ? true : false,
					'colorize' => ($primary) ? true : false,
					'roster' => (int)$value['roster'] ? true : false,
					'recruitment' => (int)$value['recruitment'] ? true : false,
					'parent' => $parent,
				);
				
				$arrClassIcons[preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['type']))] = $arrIcons;
				$arrJSONData['icons']['classes'][preg_replace("/[^a-zA-Z0-9.]/","",utf8_strtolower($value['type']))] = $arrTmpBaseIcons;
			}
			
			//Get Roles
			$arrRoles = array();
			$arrRoleIcons = array();
			$i = 1;
			foreach($a['role']['name'] as $key => $value){
				if ($value == "") continue;
				$arrRoles[$i] = $this->increase_val($a['role']['classes'][$key]);
				$arrLang['roles'][$i] = $value;
				
				//RoleIcons
				if(isset($_FILES['role']['tmp_name']['file'][$key]) && strlen($_FILES['role']['tmp_name']['file'][$key])){
					$strName = $_FILES['role']['name']['file'][$key];
					$ext = strtolower(pathinfo($strName, PATHINFO_EXTENSION));
					if(!in_array($ext, array('jpg', 'png'))) continue;
					
					$data = file_get_contents($_FILES['role']['tmp_name']['file'][$key]);
					$base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
					$arrJSONData['icons']['roles'][$i] = $base64;
						
					$arrRoleIcons[$i] = array(
						'name' => $_FILES['role']['name']['file'][$key],
						'tmp_name' => $_FILES['role']['tmp_name']['file'][$key],
					);
				} elseif($a['role']['file_base64'][$key]){
							$arrJSONData['icons']['roles'][$i] =  $a['role']['file_base64'][$key];
							$splited = explode(',', substr( $a['role']['file_base64'][$key] , 5 ) , 2);
							$mime=$splited[0];
							$data=$splited[1];

							$mime_split_without_base64=explode(';', $mime,2);
							$mime_split=explode('/', $mime_split_without_base64[0],2);
							if(count($mime_split)==2)
							{
								$extension=$mime_split[1];
								if($extension=='jpeg')$extension='jpg';
							}
							
							$output_folder = $this->pfh->FolderPath($tmpFolder, "gamebuilder");
							$strFilename = md5(rand()).'.'.$extension;
							$this->pfh->putContent($output_folder.'/'.$strFilename, base64_decode($data));
							$arrRoleIcons[$i] = array(
								'name' 		=> $strFilename,
								'tmp_name'	=> $this->pfh->FilePath($output_folder.'/'.$strFilename),
								'is_local'	=> true,
							);
					}
				
				
				$i++;
			}
			
			//Get Events
			$arrEvents = array();
			$arrEventIcons = array();
			$i = 1;
			foreach($a['events']['name'] as $key => $value){
				if ($value == "") continue;
		
				//EventIcons
				if(isset($_FILES['events']['tmp_name']['file'][$key]) && strlen($_FILES['events']['tmp_name']['file'][$key])){
					$strName = $_FILES['events']['name']['file'][$key];
					$ext = strtolower(pathinfo($strName, PATHINFO_EXTENSION));
					if(!in_array($ext, array('jpg', 'png'))) continue;
					
					$data = file_get_contents($_FILES['events']['tmp_name']['file'][$key]);
					$base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
					$arrJSONData['icons']['events'][$i] = $base64;
						
					$arrEventIcons[$i] = array(
						'name' => $_FILES['events']['name']['file'][$key],
						'tmp_name' => $_FILES['events']['tmp_name']['file'][$key],
					);
					
					
					$arrEvents[$i] = array(
						'name' => $value,
						'icon' => 'event_'.$i.'.'.$ext,
					);
				} elseif($a['events']['file_base64'][$key]){
							$arrJSONData['icons']['events'][$i] =  $a['events']['file_base64'][$key];
							$splited = explode(',', substr( $a['events']['file_base64'][$key] , 5 ) , 2);
							$mime=$splited[0];
							$data=$splited[1];

							$mime_split_without_base64=explode(';', $mime,2);
							$mime_split=explode('/', $mime_split_without_base64[0],2);
							if(count($mime_split)==2)
							{
								$extension=$mime_split[1];
								if($extension=='jpeg')$extension='jpg';
							}
							
							$output_folder = $this->pfh->FolderPath($tmpFolder, "gamebuilder");
							$strFilename = md5(rand()).'.'.$extension;
							$this->pfh->putContent($output_folder.'/'.$strFilename, base64_decode($data));
							$arrEventIcons[$i] = array(
								'name' 		=> $strFilename,
								'tmp_name'	=> $this->pfh->FilePath($output_folder.'/'.$strFilename),
								'is_local'	=> true,
							);
							
							$arrEvents[$i] = array(
							'name' => $value,
							'icon' => 'event_'.$i.'.'.$extension,
						);
					}

				$i++;
			}

			//Get Classcolors
			$arrClassColors = array();
			$i=1;
			foreach($arrClassArray[$intPrimary] as $key => $val){
				if ($val['classcolor'] != "") $arrClassColors[$i] = $val['classcolor'];
				$i++;
			}
			
			//Get Profilefields
			$arrProfilefields = array();
			
			$i = 1;
			foreach($a['profilefield']['name'] as $key => $value){
				if ($value == "") continue;
				
				$arrLangProfilefields['profilefield_'.$i] = $value;
				
				$id = 'profilefield_'.$i;
				
				$options = explode("\n", $a['profilefield']['options'][$key]);
				foreach($options as $k => $val){
					$val = trim(str_replace(array("\n", "\r"), "", $val));
					$options[$k] = $val;
				}
				
				$arrProfilefields[$id] = array(
					'type'			=> $a['profilefield']['type'][$key],
					'category'		=> 'character',
					'lang'			=> 'uc_'.$id,
					'size'			=> 40,
					'undeletable'	=> false,
					'sort'			=> $i,
					'options'		=> $options,
				);
				
				$i++;
			}

			
			//Write language file
			$language = preg_replace("/[^a-zA-Z0-9_]/","",utf8_strtolower($this->in->get('lang')));
			
			$arrLang['lang'] = array(
				$game => ucfirst($game),
			);
			
			foreach($arrClassNames as $k => $v){
				$arrLang['lang']['uc_'.$v] = ucfirst($v);
			}
			
			foreach($arrLangProfilefields as $k => $v){
				$arrLang['lang']['uc_'.$k] = $v;
			}
			
			
			$arrJSONData['common'] = array(
				'v' => $version,
				'g' => $game,
				'l' => $language,
			);
			
			foreach($arrClasses as $key => $class){
				$arrJSONData['classes'][] = $class;
			}
			
			$arrJSONData['classcolors'] = $arrClassColors;
			$arrJSONData['roles'] = $arrRoles;
			$arrJSONData['lang'] = $arrLang;
			$arrJSONData['profilefields'] = $arrProfilefields;
			$arrJSONData['events'] = $arrEvents;
			
			//Check if primary is set

			
			
			if(!isset($arrClassTypes[$intPrimary])){
				message_die('Could not find Primary Class. Please go back and check the primary class.');
			}
			
			
			
			
			//Output Game File
			$output = "<?php

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

if(!class_exists('".$game."')) {
	class ".$game." extends game_generic {	
		public \$version			= '".$version."';
		protected \$this_game	= '".$game."';
		protected \$types		= array('".implode("', '", $arrClassTypes)."', 'roles', 'filters');
		public \$langs			= array('".$language."');	
		protected static \$apiLevel = 20;
					
		protected \$class_dependencies = array(
";
			foreach($arrClasses as $class){
				$output .= var_export($class, true).", \r\n";
			}
			
			
$output .= "			
		); //end \$class_dependencies
		
		public \$default_roles = ".var_export($arrRoles, true).";
		
		protected \$class_colors = ".var_export($arrClassColors, true).";
		
		
		protected \$glang		= array();
		protected \$lang_file	= array();
		protected \$path		= '';
		protected \$filters		= array();
		public \$lang			= false;
		//Primary Classtype
		protected $".$arrClassTypes[$intPrimary]." = false;
		
				
		/* Constructor */
		public function __construct() {
			parent::__construct();
		}
				
		/* Install or Game Change Information */
		public function install(\$install=false){
		";
		if(count($arrEvents)){
			$output .= '	$arrEventIDs = array();'."\n";
			foreach($arrEvents as $key => $val){
				$output .= '	$arrEventIDs[] = $this->game->addEvent("'.$this->escapeDoubleQuotes($val['name']).'", 0, "'.$val['icon'].'");'."\n\t";
			}
			$output .= '	$this->game->updateDefaultMultiDKPPool("Default", "Default MultiDKPPool", $arrEventIDs);'."\n";
		}
		$output .="
			\$info = array();
			return \$info;
		}
		
		public function profilefields(){
			\$arrFields = ".var_export($arrProfilefields, true).";
			
			return \$arrFields;
		}

		/**
		* Initialises filters
		*
		* @param array \$langs
		*/
		protected function load_filters(\$langs){
			if(!\$this->".$arrClassTypes[$intPrimary].") {
				\$this->load_type('".$arrClassTypes[$intPrimary]."', \$langs);
			}
			foreach(\$langs as \$lang) {
				\$names = \$this->".$arrClassTypes[$intPrimary]."[\$this->lang];
				\$this->filters[\$lang][] = array('name' => '-----------', 'value' => false);
				foreach(\$names as \$id => \$name) {
					\$this->filters[\$lang][] = array('name' => \$name, 'value' => '".$arrClassNames[$intPrimary].":'.\$id);
				}
			}
		}
					
	}#class
}
?>";
			$random = md5(rand().rand());
			$output_folder = $this->pfh->FolderPath($random, "gamebuilder");		
			$this->pfh->secure_folder($random, "gamebuilder");

			//Write game file
			$this->pfh->putContent($output_folder.'/'.$filename, $output);
			
			$langOut = "<?php
if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}
					
$".$language."_array = ".var_export($arrLang, true).";
		
?>";					

			$this->pfh->FolderPath($output_folder.'/language/');
			$this->pfh->putContent($output_folder.'/language/'.$language.'.php', $langOut);
			
			//Readme File
			$strReadme = "Installation of this game: \n\n";
			$strReadme .=" - create a new subfolder called '".$game."' in folder 'games'";
			$strReadme .=" - extract the downloaded files in this new created folder. The game class and the language folder must be directly in this subfolder";
			$strReadme .=" - go to the settings area of your EQdkp Plus Adminpanel and select your game";
			
			$this->pfh->putContent($output_folder.'README.txt', $strReadme);
			
			//Package file
			$strPackage = '<?xml version="1.0" encoding="utf-8"?>
<install type="game" version="2.0.0.0">
	<folder>'.$game.'</folder>
	<name>'.$game.'</name>
	<author>Gamebuilder</author>
	<authorEmail>gamebuilder@eqdkp-plus.eu</authorEmail>
	<creationDate>'.$this->time->time.'</creationDate>
	<copyright>(C)2018 Gamebuilder</copyright>
	<version>'.$version.'</version>
	<level>stable</level>
</install>';
			$this->pfh->putContent($output_folder.'package.xml', $strPackage);
						
			$this->pfh->putContent($output_folder.'GAMEBUILDER.json', json_encode($arrJSONData));
			
			$this->pfh->secure_folder('json', 'gamebuilder');
			$this->pfh->putContent($this->pfh->FolderPath('json', "gamebuilder").$game.'_'.$version.'_'.md5(rand()).'.json', json_encode($arrJSONData));
			
			$this->pfh->FolderPath($output_folder.'/icons/');
			
			//Role Icons
			$this->pfh->FolderPath($output_folder.'/icons/roles/');
			$roleIconFolder = $output_folder.'/icons/roles/';
			foreach($arrRoleIcons as $key => $val){
				$ext = pathinfo($val['name'], PATHINFO_EXTENSION);
				if(isset($val['is_local']) && $val['is_local']){
					$this->pfh->FileMove($val['tmp_name'], $roleIconFolder.$key.'.'.$ext);
				} else {
					$this->pfh->FileMove($val['tmp_name'], $roleIconFolder.$key.'.'.$ext, true);
				}
			}
			
			//Other Icons
			$iconFolder = $output_folder.'/icons/';
			foreach($arrClassIcons as $type => $icons){
				$this->pfh->FolderPath($output_folder.'/icons/'.$type);
				foreach($icons as $key => $val){
					$ext = pathinfo($val['name'], PATHINFO_EXTENSION);
					if(isset($val['is_local']) && $val['is_local']){
						$this->pfh->FileMove($val['tmp_name'], $iconFolder.$type.'/'.$key.'.'.$ext);
					} else {
						$this->pfh->FileMove($val['tmp_name'], $iconFolder.$type.'/'.$key.'.'.$ext, true);
					}
				}
			}
			
			//Event Icons
			$iconFolder = $output_folder.'/icons/events/';
			foreach($arrEventIcons as $key => $val){
				$this->pfh->FolderPath($output_folder.'/icons/events/');
	
				$ext = pathinfo($val['name'], PATHINFO_EXTENSION);
				if(isset($val['is_local']) && $val['is_local']){
					$this->pfh->FileMove($val['tmp_name'], $iconFolder.'/event_'.$key.'.'.$ext);
				} else {
					$this->pfh->FileMove($val['tmp_name'], $iconFolder.'/event_'.$key.'.'.$ext, true);
				}	
			}
			
			
			$file = $output_folder.'/game.zip';
			//Create Zip File
			$archive = registry::register('zip', array($file));
			
			//Create the archive
			$archive->add($output_folder, $output_folder);
			$result = $archive->create();
			
			//Save Gamebuilder json
			if($this->user->is_signedin()){
				$this->pfh->secure_folder('user_json/'.$this->user->id, 'gamebuilder');
				$strUserFolder = $this->pfh->FolderPath('user_json/'.$this->user->id, "gamebuilder");
				$strFile = $game.'.json';
				
				$this->pfh->putContent($this->pfh->FolderPath('user_json', "gamebuilder").$this->user->id.'/'.$game.'.json', json_encode($arrJSONData));
			}
			
			if (file_exists($file)){
				header('Content-Type: application/octet-stream');
				header('Content-Length: '.$this->pfh->FileSize($file));
				header('Content-Disposition: attachment; filename="'.sanitize($game.'_'.$version.'.zip').'"');
				header('Content-Transfer-Encoding: binary');
				readfile($file);
				$this->pfh->Delete($file);
				$this->pfh->Delete($output_folder);
				$this->pfh->Delete( $this->pfh->FolderPath($tmpFolder, "gamebuilder"));
				exit;
			}
		}
		
		
		$this->jquery->colorpicker('bla', '');
		
		$strUserFolder = $this->pfh->FolderPath('user_json/'.$this->user->id, "gamebuilder");
		$arrUserFiles = sdir($strUserFolder);
		foreach($arrUserFiles as $key => $strFile){
			//read data
			$strContent = file_get_contents($strUserFolder.'/'.$strFile);
			$arrContent = json_decode($strContent);
			$strGame = $arrContent->common->g;
			$strVers = $arrContent->common->v;
			$intTime = filemtime($strUserFolder.'/'.$strFile);
			
			$this->tpl->assign_block_vars('requirements', array(
				'GAME' => sanitize($strGame),
				'VERSION'  => sanitize($strVers),
				'MODIFIED' => $this->time->user_date($intTime, true),
				'ID'	=> $key,
			));
		}
		
		$this->set_vars(array(
			'page_title'		=> "EQdkp Plus Gamebuilder",
			'template_file'		=> 'gamebuilder.html',
			'description'		=> 'Build your own games for EQdkp Plus with this tool.',
			'display'			=> true
		));

	}

}
?>