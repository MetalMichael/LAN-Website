$(document).ready(function() {

    //Bind click
    $("#new-submit").live("click", function() {
        tournaments.submitNew();
    });
    
    //Load entries
    tournaments.loadEntries();
    
    //Entry click
    $(".entry-row").live("click", function() {
        tournaments.clickRow(this);
    });
    
    //Buttons
        //Disable buttons
    $("#delete-tournament, #empty-signups, #view-signups").button('disable');

        //Delete button
    $("#delete-tournament").live("click", function() {
        tournaments.deleteButton();
    });
    
        //Empty button
    $("#empty-signups").live("click", function() {
        tournaments.emptyButton();
    });
    
        //View button
    $("#view-signups").live("click", function() {
        tournaments.viewButton();
    });
    
    //Date/Time pickers
    $('.time-picker').timepicker();
});

var tournaments = {
    selectedID: null,
    selectedRow: null,
    
    loadEntries: function() {
        $.get(
            UrlBuilder.buildUrl(true, 'tournaments', 'getentries'),
            function (data) {
                $("#table-body").html("");
                if (data.length > 0) {
                    for (var i = 0; i < data.length; i++) {
                        var row = data[i];
                        var string = '<div class="entry-row ' + (i % 2?'odd':'even') + ' ' + (i == data.length -1 ? 'end-entry':'') + '">';
                        string += '<span class="id">' + row.id + '</span>';
                        string += '<span class="game">' + row.game + '</span>';
                        string += '<span class="name">' + row.name + '</span>';
                        string += '<span class="team-size">' + row.team_size + '</span>';
                        string += '<span class="type">' + row.type + '</span>';
                        string += '<span class="description">' + row.description + '</span>';
                        string += '<span class="start">' + row.start_time + '</span>';
                        string += '<span class="end">' + row.end_time + '</span>';
                        string += '<span class="signup-end">' + row.signup_end + '</span>';
                        string += '<span class="signups"><input type="checkbox" class="signup-checkbox" ' + ((row.signups) ? 'checked="checked"' : '') + ' /></span>';
                        string += '<span class="visible"><input type="checkbox" class="visible-checkbox" ' + ((row.visible) ? 'checked="checked"' : '') + ' /></span>';
                        string += '<span class="current-signups">' + row.current_signups + '</span>';
                        string += '</div>';
                        $("#table-body").append(string);
                    }
                } else {
                    $("#table-body").append('<div class="no-entries odd end-entry">No tournaments found</div>');
                }
            },
            'json');
    },
    
    submitNew: function() {
        Overlay.loadingOverlay();
        $.post(
            UrlBuilder.buildUrl(true, 'tournaments', 'add'), {
                game: $("#new-game").val(),
                name: $("#new-name").val(),
                teamsize: $("#new-team-size").val(),
                type: $("#new-type").val(),
                description: $("#new-description").val(),
                start: $("#new-start").val(),
                end: $("#new-end").val(),
                signup_end: $("#new-signups-end").val(),
                signups: $("#new-signups").prop('checked'),
                visible: $("#new-visible").prop('checked'),
            },
            function (data) {
                if (data != null && data.error) {
                    Overlay.openOverlay(true, data.error);
                    return;
                }
                Overlay.openOverlay(false, "Tournament added", 1000);
                tournaments.loadEntries();
                tournaments.clearForm();
            },
            'json');
    },
    
    clearForm: function() {
        //$("#add-tournament input").val('');
    },
    
    clickRow: function(row) {
        this.selectedID = $(row).find(".id").html();
        this.selectedRow = $(row);
        $(".entry-row").removeClass("selected-row");
        $(row).addClass("selected-row");

        $("#delete-tournament, #empty-signups, #view-signups").button('enable');
    },
    
    deleteButton: function() {
        if(!this.selectedID) return;
        
        Overlay.openOverlay(true, 'Are you sure you wish to delete this tournament? This cannot be undone!<br /><button id="confirm-delete">I am sure</button>');
        $("#confirm-delete").button();
        $("#confirm-delete").click(function() {
            $.post(
                UrlBuilder.buildUrl(true, "tournaments", "delete"),
                { tournament_id: tournaments.selectedID },
                function (data) {
                    if (data != null && data.error) {
                        Overlay.openOverlay(true, data.error);
                        return;
                    }
                    tournaments.loadEntries();
                    entry_id = false;
                    $(".entry-row").removeClass("selected-row");
                    $("#delete-tournament, #empty-signups, #view-signups").button('disable');
                    Overlay.openOverlay(false, "Tournament deleted", 1000);
                },
                'json');
        });
    },
    
    emptyButton: function() {
        if(!this.selectedID) return;
        if(this.selectedRow.find(".current-signups").html() == '0') {
            Overlay.openOverlay(false, "This tournament has no signups!", 1000);
            return;
        }
        
        Overlay.openOverlay(true, 'Are you sure you wish to empty signups for this tournament? This cannot be undone!<br /><button id="confirm-delete">I am sure</button>');
        $("#confirm-delete").button();
        $("#confirm-delete").click(function() {
            $.post(
                UrlBuilder.buildUrl(true, "tournaments", "empty"),
                { tournament_id: tournaments.selectedID },
                function (data) {
                    if (data != null && data.error) {
                        Overlay.openOverlay(true, data.error);
                        return;
                    }
                    tournaments.loadEntries();
                    entry_id = false;
                    $(".entry-row").removeClass("selected-row");
                    $("#delete-tournament, #empty-signups, #view-signups").button('disable');
                    Overlay.openOverlay(false, "Tournament signups emptied", 1000);
                },
                'json');
        });
    },
    
    viewButton: function() {
        if(!this.selectedID) return;
        window.location = UrlBuilder.buildUrl(true, 'tournaments', 'view', {id:this.selectedID});
    }
}