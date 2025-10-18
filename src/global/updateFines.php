<?php 
// Updating fine amounts for all existing transactions in the database
$sql1="SELECT * FROM transactions";
$result=$conn->query($sql1);
if($result->num_rows>0)
{
     while ($row = $result->fetch_assoc()) 
     {
          $tid=$row['tid'];
          $returnDate=$row['returnDate'];
          $lastFinePaymentDate=$row['lastFinePaymentDate'];

          $today = new DateTime(); // current date
          if (!empty($lastFinePaymentDate) && $lastFinePaymentDate > $returnDate) // if fine was paid after the returnDate but the book was not returned, then calculating fine from the day the fine was last paid on
          {
               $rDate = DateTime::createFromFormat('Y-m-d', $lastFinePaymentDate);
          }
          else // if fine was never paid, then calculating fine from the returnDate
          {
               $rDate = DateTime::createFromFormat('Y-m-d', $returnDate);
          }
          if ($rDate !== false && $today > $rDate) 
          {
               $interval = $today->diff($rDate);
               $daysLate = $interval->days;
               $updatedFine = ($daysLate > 0) ? $daysLate * 10 : 0;
               $sql2="UPDATE transactions SET fine='$updatedFine' WHERE tid='$tid'";
               if ($conn->query($sql2)===FALSE)
               {
                    echo "Error: ".$sql2."<br>".$conn->error;
               }    
          } 
     }
}
?>