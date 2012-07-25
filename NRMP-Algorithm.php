<?

class Match {

	public $candidatearray;
	public $programarray;

	public function __construct($candidatearray, $programarray) {

		$this->candidatearray = $candidatearray;
		$this->programarray = $programarray;

		$this->RunMatch();
	} // function __construct


	public function RunMatch() {

		// go through each candidate
		for ($x = 0; $x < count($this->candidatearray); $x++) { 

			$this->candidatearray[$x]->FindMatch();

		} // go through each candidate

		$this->PrintResult();

	} // function RunMatch


	public function PrintResult() {

		echo "\n\n";
		echo "FINAL PROGRAM MATCHLIST\n------------------------------\n";

		// go through each program
		for ($x = 0; $x < count($this->programarray); $x++) { 
			
			echo $this->programarray[$x]->name . ": ";
			
			// go through program's tentmatcharray and print matched candidates
			for ($y = 0; $y < count($this->programarray[$x]->tentmatcharray); $y++) { 

				echo $this->programarray[$x]->tentmatcharray[$y]->name;
				echo "(" . (array_search($this->programarray[$x]->tentmatcharray[$y], $this->programarray[$x]->ranklist) + 1) . ") ";

			} // go through each program's tentmatcharray

			echo "\n";

		} // go through each program



		echo "\n\n";
		echo "FINAL CANDIDATE RESULTS\n------------------------------\n";

		// go through each candidate
		for ($x = 0; $x < count($this->candidatearray); $x++) { 

			echo $this->candidatearray[$x]->name . ": ";
			if ($this->candidatearray[$x]->tentmatch == null)
				echo "Unmatched";
			else {
				echo $this->candidatearray[$x]->tentmatch->name;
				echo "(" . (array_search($this->candidatearray[$x]->tentmatch, $this->candidatearray[$x]->ranklist) + 1) . ")";
			}
			echo "\n";

		} // go through each candidate

	} // function PrintResult


} // class Match




class Candidate {

	public $name;
	public $ranklist;
	public $tentmatch;
	public $matchresult;


	public function __construct($name) {
		$this->name = $name;
		//echo "Candidate: $this->name\n";

	} // function __construct


	public function FindMatch() {

		// go through ranklist starting from the beginning
		for ($i=0; $i< count($this->ranklist); $i++) {

			echo $this->name . " seeks match at " . $this->ranklist[$i]->name;
			echo " (ranked " . ($i + 1) . " by " . $this->name . ").\n";

			// run Match
			$this->matchresult = $this->ranklist[$i]->TryMatch($this);

			// if candidate is able to find a match at the program, add to tentmatch
			if ( (get_class($this->matchresult) == "Candidate") || ($this->matchresult === true) ) {

				echo $this->name . " tentatively matched with " . $this->ranklist[$i]->name . ".\n\n";

				$this->tentmatch = $this->ranklist[$i];

				// if popped candidate is returned
				if (get_class($this->matchresult) == "Candidate") {

					echo "Finding new match for " . $this->matchresult->name . ".\n";

					// reset tentmatch for candidate
					$this->matchresult->tentmatch = null;

					// find match for popped candidate
					$this->matchresult->FindMatch();

				} // if popped candidate is returned

				return true;

			} //  if candidate is able to find a match at the prorgram...


			// else, if the match result is false...
			else if ($matchresult === false) {

				echo $this->name . " not matched at " . $this->ranklist[$i]->name . ".\n\n";

			} // else if matchresult is false

		} // go through ranklist starting from beginning


		echo "Reached end of $this->name's ranklist, $this->name remains unmatched.\n\n";

	} // function FindMatch

} // class Candidate



class Program {

	public $spots;
	public $tentmatcharray;
	public $ranklist;
	public $tentmatchlast;
	public $poppedcandidate;
	public $lowestmatchrank;


	public function __construct($name, $spots) {
		$this->name = $name;
		$this->spots = $spots;
	} // function __construct



	public function TryMatch($candidate) {

		// if candidate is not on program's ranklist, reject candidate
		if (in_array($candidate, $this->ranklist) == false) {
			echo "$candidate->name is not on $this->name's ranklist. $candidate->name not matched at $this->name.\n\n";
			return false;
		} // reject is candidate is not on program's ranklist

		// otherwise, candidate is on program's ranklist
		echo "$candidate->name is ranked " . (array_search($candidate, $this->ranklist)+1) . " on $this->name's ranklist.\n";
		echo "$this->name has $this->spots total spots, " . count($this->tentmatcharray) . " of which are tentatively taken.\n";
		$this->PrintTentMatchArray();

		// if ranklist is empty or has empty spaces
		if ( ($this->tentmatchlast == null) || (count($this->tentmatcharray) < $this->spots) ) {

			// add candidate to program's tentmatcharray
			$this->AddToTentMatchArray($candidate);

			$this->ResetTentMatchLast();

			return true;

		} // if ranklist is empty or has open spaces


		// else if program spots are full and candidate is ranked higher than the lowest ranked candidate currently matched
		else if (
		(count($this->tentmatcharray) == $this->spots) && 
			(array_search($candidate, $this->ranklist) < array_search($this->tentmatchlast, $this->ranklist))
		) {

			echo "$candidate->name is ranked higher by $this->name than " . $this->tentmatchlast->name . ".\n";

			$this->RemoveLastPlaceCandidate();

			$this->AddToTentMatchArray($candidate);

			$this->ResetTentMatchLast();

			return $this->poppedcandidate;
		} // if program spots are full and candidate is ranked higher by program than program's current last place candidate

		// else match not made
		else {

			echo "$candidate->name ranked too low on $this->name's ranklist. $candidate->name not matched at $this->name.\n\n";
			return false;

		} // else match not made, return false


	} // function TryMatch



	public function RemoveLastPlaceCandidate() {

		array_splice($this->tentmatcharray, array_search($this->tentmatchlast, $this->tentmatcharray), 1);
		echo $this->tentmatchlast->name . " removed from " . $this->name . "'s tentative match list.\n";
		$this->PrintTentMatchArray();
		$this->poppedcandidate = $this->tentmatchlast;
		//$this->tentmatchlast = null;
	}


	public function ResetTentMatchLast() {

		$this->tentmatchlast = $this->tentmatcharray[0];
		$this->lowestmatchrank = array_search($this->tentmatcharray[0], $this->ranklist);

		// loop through tentmatcharray
		for ($i = 1; $i < count($this->tentmatcharray); $i++) {

			// if tentmatcharray's nth element's ranking is higher
			if (array_search($this->tentmatcharray[$i], $this->ranklist) > $this->lowestmatchrank) {
				$this->tentmatchlast = $this->tentmatcharray[$i];
				$this->lowestmatchrank = array_search($this->tentmatcharray[$i], $this->ranklist);
			} // if
		} // for
		
		echo $this->tentmatchlast->name . " is the lowest ranking candidate on " . $this->name . "'s ranklist.\n"; 
		
	} // FindLastPlaceTentMatchCandidate



	public function AddToTentMatchArray($candidate) {

		// add candidate to program's tentmatcharray
		$this->tentmatcharray[] = $candidate;
		echo "$candidate->name added to $this->name's tentative match list.\n";
		$this->PrintTentMatchArray();
		// return true;

	} // function AddToTentMatchArray



	public function PrintTentMatchArray() {

		echo "$this->name's tentative match list: ";

		for ($i=0; $i < count($this->tentmatcharray); $i++) {
			echo " " . $this->tentmatcharray[$i]->name; 
			echo "(" . (array_search($this->tentmatcharray[$i], $this->ranklist) + 1). ")";
		} // for each item in tentmatcharray
		echo "\n";
		return true;
	} // function PrintTentMatchArray



} // class Program



// initialize candidates, programs and respective ranklist objects
$candidates[0] = new Candidate("Anderson");
$candidates[1] = new Candidate("Brown");
$candidates[2] = new Candidate("Chen");
$candidates[3] = new Candidate("Davis");
$candidates[4] = new Candidate("Eastman");
$candidates[5] = new Candidate("Ford");
$candidates[6] = new Candidate("Garcia");
$candidates[7] = new Candidate("Hassan");

$programs[0] = new Program("Mercy", 2);
$programs[1] = new Program("City", 2);
$programs[2] = new Program("General", 2);
$programs[3] = new Program("State", 2);

$candidates[0]->ranklist = array($programs[1]);
$candidates[1]->ranklist = array($programs[1], $programs[0]);
$candidates[2]->ranklist = array($programs[1], $programs[0]);
$candidates[3]->ranklist = array($programs[0], $programs[1], $programs[2], $programs[3]);
$candidates[4]->ranklist = array($programs[1], $programs[0], $programs[3], $programs[2]);
$candidates[5]->ranklist = array($programs[1], $programs[2], $programs[0], $programs[3]);
$candidates[6]->ranklist = array($programs[1], $programs[0], $programs[3], $programs[2]);
$candidates[7]->ranklist = array($programs[3], $programs[1], $programs[0], $programs[2]);

$programs[0]->ranklist = array($candidates[2], $candidates[6]);
$programs[1]->ranklist = array($candidates[6], $candidates[7], $candidates[4], $candidates[0], $candidates[1], $candidates[2], $candidates[3], $candidates[5]);
$programs[2]->ranklist = array($candidates[1], $candidates[4], $candidates[7], $candidates[0], $candidates[2], $candidates[3], $candidates[6]);
$programs[3]->ranklist = array($candidates[1], $candidates[4], $candidates[0], $candidates[2], $candidates[7], $candidates[5], $candidates[3], $candidates[6]);


// run program
$a = new Match($candidates, $programs); 



?>