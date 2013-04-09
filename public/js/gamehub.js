var searchtimer = null;
var clickedlobby = false;
$(document).ready(function() {

    //Editor binds
    $(document).on({ click: function() {
        $("#overlay .other-game").fadeOut(300);
        $("#overlay .steam-input-container").fadeIn(300);
        $("#overlay .icon-section").slideUp(200);
    }}, ".steam-game-label");
    $(document).on({ click: function() {
        $("#overlay .steam-input-container").fadeOut(300);
        $("#overlay .steam-search").slideUp(200);
        $("#overlay .steam-search").html('');
        $("#overlay .steam-game").val('');
        $("#overlay .icon-section").slideDown(200);
        $("#overlay .other-game").fadeIn(300);
    }}, ".other-game-label");
    $(document).on({ click: function() {
        $("#overlay .max-players").parent().fadeOut(300);
    }}, ".unlimited-players-label");
    $(document).on({ click: function() {
        $("#overlay .max-players").parent().fadeIn(300);
    }}, ".max-players-label");
    $(document).on({ click: function() {
        $("#overlay .password").fadeOut(300);
    }}, ".nopassword-label");
    $(document).on({ click: function() {
        $("#overlay .password").fadeIn(300);
    }}, ".password-label");
    //Steam game search
    $(document).on({ input: function() {
        clearTimeout(searchtimer);
        $("#overlay .steam-search").html('');
        $("#overlay .steam-search").slideUp(200);
        if ($(this).val().length > 0) {
            searchtimer = setTimeout(function() {
                $("#overlay .steam-search").slideUp(200);
                $("#overlay .steam-input-container .spinner").fadeIn(200);
                $.get(
                    UrlBuilder.buildUrl(false, "gamehub", "searchsteamgames", { game: $("#overlay .steam-game").val() }),
                    function(data) {
                        $("#overlay .steam-search").html('');
                        if ($("#overlay #select-steam-game").is(":checked")) {
                            if (data.length > 0) {
                                for (i = 0; i < data.length; i++) {
                                    $("#overlay .steam-search").append('<div class="search-result" value="' + data[i].appid + '"><img src="' + data[i].icon + '" /><div class="name">' + data[i].name + '</div></div>');
                                }
                                setTimeout(function() { $("#overlay .steam-search").mCustomScrollbar({
                                    scrollButtons: { enable: true },
                                    theme: "dark"
                                });}, 210);
                            } else {
                                $("#overlay .steam-search").append('<div class="search-noresults">No results found for that search</div>');
                            }
                            $("#overlay .steam-search").slideDown(200);
                        }
                        $("#overlay .steam-input-container .spinner").fadeOut(200);

                    },
                    'json');
                }, 500);
        }
        
    }}, ".steam-game");
    $(document).on({ click: function() {
        $(".result-selected").removeClass("result-selected");
        $(this).addClass("result-selected");
    }}, ".search-result");
    
    //Scroll bars
    $("#lobbies, #info-container").mCustomScrollbar({
        scrollButtons: { enable: true },
        theme: "dark"
    });
    
    //Create lobby bind
    $(".create-lobby").click(function() {
        LobbyClient.openLobbyEditor();
    });
    
    //Edit lobby bind
    $(".edit-lobby").click(function() {
        LobbyClient.openLobbyEditor(true);
    });
    
    //Leave lobby bind
    $(".leave-lobby").click(function() {
        LobbyClient.leaveLobby();
    });
    
    //Join lobby bind
    $(document).on({ click: function() {
        clickedlobby = true;
        LobbyClient.joinLobby($(this).attr('value'));
    }}, ".lobby");
    
    //Submit lobby editor bind
    $(document).on({ click: function() {
        LobbyClient.submitLobbyEditor();
    }}, ".submit-lobbyeditor");
    
    //Lobby list mouse over binds
    $(document).on({ mouseover: function() {
        LobbyClient.fillLobbyInfo(LobbyClient.lobbies[$(this).attr('value')]);
    }}, ".lobby");
    $(document).on({ mouseleave: function() {
        if (clickedlobby) {
            clickedlobby = false;
        } else {
            $("#lobby-info").fadeOut(100);
            $("#lobby-info").attr('value', '');
        }
    }}, ".lobby");
    
    LobbyClient.connect();
    
});

var LobbyClient = {

    connection: null,
    lobby: null,
    lobbies: {},

    connect: function () {
    
    	//Get connection details
        $.get(
            UrlBuilder.buildUrl(false, 'gamehub', 'getlobbydetails'),
            function (data) {
            
                //Disabled?
                if (data && data.disabled == 1) {
                    return;
                }
                
                LobbyClient.connection = new WebSocket(data.url);
                
                LobbyClient.connection.onclose = function() {
                    console.log("Lobby server connection closed");
                    LobbyClient.connection = null;
                    $("#connecting").slideDown(200);
                    $("#lobby-client").slideUp(200);
                    setTimeout(function() { LobbyClient.connect(); }, 3000);
                };
                
                LobbyClient.connection.onopen = function() {
                    console.log("Lobby server connection opened");
                    LobbyClient.sendLobbyCommand("init", "");
                    $("#connecting").slideUp(200);
                    $("#lobby-client").slideDown(200);
                }
                
                LobbyClient.connection.onerror = function(error) {
                    console.log("Lobby Error: " + error);
                }
                
                LobbyClient.connection.onmessage = function(e) {
                    LobbyClient.handleLobbyCommand(e.data);
                }
                
            },
            'json');
        
    },
    
    handleLobbyCommand: function (data) {
    
        //Extract command and payload
        var command = data.substr(0, data.indexOf(":")).toLowerCase();
        var payload = JSON.parse(data.substr(data.indexOf(":") + 1));
        
        console.log("Received lobby command: " + command);
        console.log(payload);
        
        switch (command) {
        
            //Init command - sends active lobby, lobby list and global chat history
            case 'init':
                this.lobbies = {};
                this.lobby = null;
                
                //Load active lobby?
                if (payload.activelobby !== false) {
                    this.loadLobby(payload.activelobby);
                }
                //If not, load lobby list
                else {
                    $("#in-lobby").fadeOut(200);
                    $("#lobby-info").fadeOut(100);
                    $("#lobbies-container").fadeIn(200);
                
                    $("#lobbies .mCSB_container").html('');
                    for (var lobby in payload.lobbies) {
                        this.updateLobbyList(payload.lobbies[lobby]);
                    }
                
                }
                
                //Load global chat history
                
                break;
                
            //Create lobby - reciprocated method for reporting creation errors
            case "createlobby":
            
                if (payload.error != null) {
                    $("#overlay .error").html(payload.error).slideDown(200);
                    $("#overlay .loading").slideUp(200);
                    $("#overlay .submit-lobbyeditor").slideDown(200);
                } else {
                    Overlay.closeOverlay();
                }
            
                break;
                
            //Leave active lobby
            case 'leavelobby':
            
                this.lobby = null;
                this.sendLobbyCommand('init', "");
                $("#in-lobby").fadeOut(200);
                $("#lobbies-container").fadeIn(200);
                
                break;
                
            //Delete lobby
            case 'deletelobby':
                
                delete this.lobbies[payload.lobbyid];
                $("#lobbies .lobby[value='" + payload.lobbyid + "']").remove();
            
                break;
                
            //Join lobby command
            case 'joinlobby':
            
                //Error?
                if (payload.error != null) {
                    return Overlay.openOverlay(true, payload.error);
                }
                Overlay.closeOverlay();
                this.loadLobby(payload);
            
                break;
            
            //New information for a lobby
            case 'updatelobby':
            
                //If we are updating active lobby
                if (this.lobby != null) {
                    this.loadLobby(payload, true);
                }
                //Else update lobby list
                else {
                    this.updateLobbyList(payload);
                }
            
                break;
            
            //Message received for lobby chat
            case 'sendlobbychat':
            
                break;
            
            //Message received for global chat
            case 'sendglobalchat':
            
                break;
                
            default:
                console.log("Invalid command received from lobby server");
                break;
        }
    
    },
    
    updateLobbyList: function (lobby) {
        //If lobby isn't in list
        if (this.lobbies[lobby.lobbyid] == null) {
            var o = '<div class="lobby" value=' + lobby.lobbyid + '><div class="image"><img src="' + lobby.icon + '" /></div><div class="title">' + lobby.title + '</div><div class="game">' + lobby.game + '</div>';
            if (lobby.locked == 1) o += '<div class="locked"></div>';
            else o += '<div class="locked" style="display: none;"></div>';
            if (lobby.playerlimit == 0) o += '<div class="players">&infin;</div>';
            else o += '<div class="players">' + lobby.contacts.length + '/' + lobby.playerlimit + '</div>';
            o += '</div>';
            $("#lobbies .mCSB_container").append(o);
        }
        //Else, update it
        else {
            var obj = $(".lobby[value='" + lobby.lobbyid + "']");
            obj.find(".image img").attr('src', lobby.icon);
            obj.find(".title").html(lobby.title);
            obj.find(".game").html(lobby.game);
            if (lobby.locked) obj.find(".locked").fadeIn(200);
            else obj.find(".locked").fadeOut(200);
            if (lobby.playerlimit == 0) obj.find(".players").html("&infin;");
            else obj.find(".players").html(lobby.contacts.length + '/' + lobby.playerlimit);
            
            //Check if lobby info pane needs updating
            if ($("#lobby-info").attr('value') == lobby.lobbyid) {
                this.fillLobbyInfo(lobby);
            }  
        }
        this.lobbies[lobby.lobbyid] = lobby;
    },
    
    loadLobby: function (lobby, softupdate) {
        this.lobby = lobby;
        this.fillLobbyInfo(lobby);
        $("#in-lobby .box-title").html(lobby.title);
        if (softupdate == null || softupdate == false) {
            $("#lobbies-container").fadeOut(200);
            $("#in-lobby").fadeIn(200);
            //TODO: lobby history
        }
    },
    
    fillLobbyInfo: function (lobby) {
        $("#lobby-info").attr('value', lobby.lobbyid);
        $("#lobby-info .title").html(lobby.title);
        $("#lobby-info .image img").attr('src', lobby.icon);
        $("#lobby-info .game").html(lobby.game);
        $("#lobby-info .description").html(lobby.description);
        if (lobby.locked == 1) $("#lobby-info .locked").fadeIn(100);
        else $("#lobby-info .locked").fadeOut(100);
        $("#lobby-info .leader").html(lobby.leader.name);
        if (lobby.playerlimit == 0) $("#lobby-info .playerlimit").html("Unlimited");
        else $("#lobby-info .playerlimit").html(lobby.contacts.length + "/" + lobby.playerlimit);
        $("#lobby-info .players ul").html('');
        for (var i = 0; i < lobby.contacts.length; i++) $("#lobby-info .players ul").append('<li>' + lobby.contacts[i].name + '</li>');
        
        $("#lobby-info").fadeIn(100);
    },
    
    sendLobbyCommand: function (command, message) {
        if (this.connection == null) {
            console.log("Error: attempting to send lobby command without connection");
            return;
        }
        if (typeof(message) === 'object') {
            message = JSON.stringify(message);
        }
        console.log("Sending lobby command: " + command + ":" + message);
        this.connection.send(command + ":" + message);
    },

    openLobbyEditor: function (edit) {
        Overlay.openOverlay(true, $("#lobbyeditor-container").html());
        $("#overlay .max-players").spinner({ min: 2, max: 50 });
        if (edit != null && edit == true) {
            $("#overlay .submit-lobbyeditor").html('Save Lobby');
            $("#overlay .title").val(this.lobby.title);
            if (this.lobby.steam == 1) {
                $("#overlay .steam-game").val(this.lobby.game);
                $("#overlay .steam-game").trigger('input');
            } else {
                $("#overlay .other-game-label").trigger('click');
                $("#overlay .other-game").val(this.lobby.game);
                $("#overlay .custom-icon").val(this.lobby.icon);
            }
            if (this.lobby.playerlimit == 0) {
                $("#overlay .unlimited-players-label").trigger('click');
            } else {
                $("#overlay .max-players").val(this.lobby.playerlimit);
            }
            if (this.lobby.locked) $("#overlay .password-label").trigger('click');
            $("#overlay .description").val(this.lobby.description);
        } else {
            $("#overlay .submit-lobbyeditor").html('Create Lobby');
        }
        $("#overlay .submit-lobbyeditor").button();
    },
    
    submitLobbyEditor: function () {
    
        var obj = {
            password: "",
            maxplayers: -1,
            icon: "",
            game: "",
            title: "",
            description: $("#overlay .description").val(),
            steam: false
        };
        
        var error = null;
        
        //Password
        if ($("#overlay #select-password").is(":checked") && $.trim($("#overlay .password").val()).length < 3) {
            error = "Invalid password - must be at least 3 characters in length";
        } else if ($("#overlay #select-password").is(":checked")) {
            obj.password = $("#overlay .password").val();
        }
        
        //Players
        if ($("#overlay #select-max-players").is(":checked")) {
            obj.maxplayers = $("#overlay .max-players").val();
        }
        
        //Game + icon
        if ($("#overlay #select-other-game").is(":checked") && $.trim($("#overlay .other-game").val()).length == 0) {
            error = "Invalid game provided - must be at least 1 character in length";
        } else if ($("#overlay #select-other-game").is(":checked")) {
            obj.game = $("#overlay .other-game").val();
            obj.icon = $("#overlay .custom-icon").val();
            obj.steam = false;
        } else if ($("#overlay #select-steam-game").is(":checked") && $("#overlay .result-selected").length == 0) {
            error = "Please search and select a Steam game or select 'Other'";
        } else {
            obj.game = $("#overlay .result-selected").find(".name").html();
            obj.icon = "http://cdn3.steampowered.com/v/gfx/apps/" + $("#overlay .result-selected").attr('value') + "/header_292x136.jpg";
            obj.steam = true;
        }
        
        //Title
        if ($.trim($("#overlay .title").val()).length == 0) {
            error = "Invalid title provided - must be at least 1 character in length";
        } else {
            obj.title = $("#overlay .title").val();
        }
        
        //Error check
        if (error != null) {
            $("#overlay .error").html(error).slideDown(200);
            return;
        } else {
            $("#overlay .error").slideUp(200);
        }
        
        $("#overlay .submit-lobbyeditor").slideUp(200);
        $("#overlay .loading").slideDown(200);
    
        //New lobby
        if ($("#overlay .submit-lobbyeditor").find('span').html() == 'Create Lobby') {
            this.sendLobbyCommand("createlobby", obj);
        }
        //Edit existing lobby
        else {
            obj.lobbyID = this.lobby.lobbyid;
            this.sendLobbyCommand("editlobby", obj);
        }
    
    },
    
    leaveLobby: function () {
        this.sendLobbyCommand("leavelobby", "");
    },
    
    joinLobby: function (lobbyId) {
        var lobby = this.lobbies[lobbyId];
        if (lobby.locked) {
            Overlay.openOverlay(true, 'Password: <input type="password" class="enter-password"/><button class="submit-password">Join</button>');
            $(".submit-password").button().bind('click', { lobbyId: lobbyId }, function(event) {
                LobbyClient.sendLobbyCommand("joinlobby", { lobbyID: event.data.lobbyId, password: $(".enter-password").val() });
                Overlay.loadingOverlay();
            });
        } else {
            LobbyClient.sendLobbyCommand("joinlobby", { lobbyID: lobbyId, password: "" });
        }
    },
    
    sendLobbyChat: function (message) {
    
    },
    
    sendGlobalChat: function(message) {
    
    }

}