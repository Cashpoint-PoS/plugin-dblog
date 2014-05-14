<?
plugins_register_backend($plugin,array("icon"=>"icon-document-stroke"));

class DBLog extends DBObj{
  protected static $__table="log";
}

//Show list of all objects
function dblog_list() {
?>
  <div class="grid-24">
  <div class="widget widget-table">
  <div class="widget-content">
    <table class="table table-striped table-bordered data-table">
      <thead><tr>
        <th>ID</th>
        <th>Betroffenes Objekt</th>
        <th>Typ</th>
        <th>Daten</th>
        <th>Erstellt von</th>
        <th>Erstellt</th>
        <th>Letzter Bearbeiter</th>
        <th>Letzte Bearbeitung</th>
        <th>Aktion</th>
      </tr></thead>
      <tbody>
<?
  $q=DBLog::getAll();
  $total=sizeof($q);
  $discarded=0;
  if($total>0) {
    foreach($q as $r) {
      //discard entries which we are not allowed to read
      if(!acl_check("dblog",$r->id,"r")) {
        $discarded++;
        continue;
      }
      switch($r->subject) {
        case "program":
          $r->subject="Programm";
        break;
        default:
          //dont do anything
        break;
      }
      $data=unserialize($r->data);
      switch($r->type) {
        case "dbchange":
        case "dbcreate":
          $r->type=($r->type==="dbchange") ? "Datenbank-Änderung" : "Datenbank-Neuerstellung";
          $summary="";
          foreach($data as $field=>$change)
            $summary.=sprintf("Feld %s: von \"%s\" nach \"%s\"\n",$field,
              esc($change["from"]),
              esc($change["to"]));
          $summary=str_replace("\n","<br />",$summary);
        break;
        case "exception":
          $r->type="Fehler";
          $summary=esc($data["message"]);
        break;
        default:
          $summary="Keine Zusammenfassung möglich.";
      }
?>
        <tr class="gradeA">
          <td><?= $r->id ?></td>
          <td><?= esc($r->subject) ?></td>
          <td><?= esc($r->type) ?></td>
          <td><?= $summary ?></td>
          <td><?= esc($r->creator["name"]) ?></td>
          <td><?= esc($r->create_time) ?></td>
          <td><?= esc($r->last_editor["name"]) ?></td>
          <td><?= esc($r->modify_time) ?></td>
          <td>
            <a title="Details" href="be_index.php?mod=user&amp;sub=users&amp;action=view&amp;id=<?= $r->id ?>"><span class="icon-eye"></span></a>
          </td>
        </tr>
<?
    }
  }
?>
        </tbody>
    </table>
    <? if($discarded>0) { ?>
    <div class="details">
      <?=$total?> Einträge insgesamt, davon <?=$discarded?> wegen ACL-Beschränkungen nicht angezeigt.
    </div>
    <? } ?>
  </div> <!-- widget-content -->
  </div> <!-- widget -->
  </div> <!-- grid -->
<?
}
plugins_register_backend_handler($plugin,"","list","dblog_list");
