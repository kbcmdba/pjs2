/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017, 2026 Kevin Benton - kbenton at bentonfam dot org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

// Track which entity the modal is currently showing notes for
var notesAppliesToTable = null ;
var notesAppliesToId = null ;

/**
 * Open the notes modal for a given entity.
 *
 * @param {String} appliesToTable  'job', 'company', 'contact', etc.
 * @param {String} appliesToId     The entity's ID
 * @param {String} label           Display label for the modal title
 */
function openNotesModal( appliesToTable, appliesToId, label ) {
    notesAppliesToTable = appliesToTable ;
    notesAppliesToId = appliesToId ;
    var overlay = document.getElementById( 'notesOverlay' ) ;
    var title   = document.getElementById( 'notesTitle' ) ;
    title.innerHTML = 'Notes for ' + escapeHtml( label ) ;
    overlay.style.display = 'block' ;
    loadNotes() ;
}

/**
 * Close the notes modal.
 */
function closeNotesModal() {
    var overlay = document.getElementById( 'notesOverlay' ) ;
    overlay.style.display = 'none' ;
    notesAppliesToTable = null ;
    notesAppliesToId = null ;
}

/**
 * Load notes from the server and render them in the modal.
 */
function loadNotes() {
    var data = 'appliesToTable=' + encodeURIComponent( notesAppliesToTable )
             + '&appliesToId=' + encodeURIComponent( notesAppliesToId ) ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetNotes.php', data, 'notesList', true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        var container = document.getElementById( 'notesList' ) ;
        if ( jsonObj.result !== 'OK' ) {
            container.innerHTML = '<p style="color: red;">Error loading notes.</p>' ;
            return ;
        }
        var html = '' ;
        var notes = jsonObj.notes ;
        if ( notes.length === 0 ) {
            html = '<p style="color: #888;">No notes yet.</p>' ;
        } else {
            for ( var i = 0 ; i < notes.length ; i++ ) {
                var n = notes[ i ] ;
                html += '<div class="note-item" id="note-' + n.id + '">'
                      + '<div class="note-meta">'
                      + '<span>' + escapeHtml( n.created ) + '</span>'
                      + ' <a href="#" onclick="editNote(' + n.id + '); return false;">Edit</a>'
                      + ' <a href="#" onclick="deleteNote(' + n.id + '); return false;" style="color: red;">Delete</a>'
                      + '</div>'
                      + '<div class="note-text" id="note-text-' + n.id + '">' + escapeHtml( n.noteText ) + '</div>'
                      + '</div>' ;
            }
        }
        container.innerHTML = html ;
        // Update the count badge on the page
        updateNoteCount( notesAppliesToTable, notesAppliesToId, notes.length ) ;
    } ) ;
}

/**
 * Show an inline edit form for an existing note.
 *
 * @param {Number} noteId
 */
function editNote( noteId ) {
    var textDiv = document.getElementById( 'note-text-' + noteId ) ;
    var currentText = textDiv.innerText ;
    var noteItem = document.getElementById( 'note-' + noteId ) ;
    noteItem.innerHTML = '<textarea id="note-edit-' + noteId + '" rows="4" style="width: 100%; box-sizing: border-box;">'
                       + escapeHtml( currentText )
                       + '</textarea>'
                       + '<div style="margin-top: 4px;">'
                       + '<button onclick="saveEditNote(' + noteId + ')">Save</button> '
                       + '<button onclick="loadNotes()">Cancel</button>'
                       + '</div>' ;
}

/**
 * Save an edited note.
 *
 * @param {Number} noteId
 */
function saveEditNote( noteId ) {
    var textarea = document.getElementById( 'note-edit-' + noteId ) ;
    var noteText = textarea.value.trim() ;
    if ( noteText === '' ) {
        alert( 'Note text cannot be empty.' ) ;
        return ;
    }
    var data = 'id=' + encodeURIComponent( noteId )
             + '&noteText=' + encodeURIComponent( noteText ) ;
    doLoadAjaxJsonResultWithCallback( 'AJAXUpdateNote.php', data, noteId, true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.result === 'OK' ) {
            loadNotes() ;
        } else {
            alert( 'Failed to update note: ' + ( jsonObj.error || 'Unknown error' ) ) ;
        }
    } ) ;
}

/**
 * Delete a note after confirmation.
 *
 * @param {Number} noteId
 */
function deleteNote( noteId ) {
    if ( ! confirm( 'Delete this note?' ) ) return ;
    var data = 'id=' + encodeURIComponent( noteId ) ;
    doLoadAjaxJsonResultWithCallback( 'AJAXDeleteNote.php', data, noteId, true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.result === 'OK' ) {
            loadNotes() ;
        } else {
            alert( 'Failed to delete note: ' + ( jsonObj.error || 'Unknown error' ) ) ;
        }
    } ) ;
}

/**
 * Add a new note from the modal's input area.
 */
function addNote() {
    var textarea = document.getElementById( 'noteNewText' ) ;
    var noteText = textarea.value.trim() ;
    if ( noteText === '' ) {
        alert( 'Note text cannot be empty.' ) ;
        return ;
    }
    var data = 'appliesToTable=' + encodeURIComponent( notesAppliesToTable )
             + '&appliesToId=' + encodeURIComponent( notesAppliesToId )
             + '&noteText=' + encodeURIComponent( noteText ) ;
    doLoadAjaxJsonResultWithCallback( 'AJAXAddNote.php', data, 'notesList', true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.result === 'OK' ) {
            textarea.value = '' ;
            loadNotes() ;
        } else {
            alert( 'Failed to add note: ' + ( jsonObj.error || 'Unknown error' ) ) ;
        }
    } ) ;
}

/**
 * Update the note count badge on the listing page.
 *
 * @param {String} appliesToTable
 * @param {String} appliesToId
 * @param {Number} count
 */
function updateNoteCount( appliesToTable, appliesToId, count ) {
    var badge = document.getElementById( 'noteCount-' + appliesToTable + '-' + appliesToId ) ;
    if ( badge ) {
        badge.innerHTML = count ;
    }
}

// Close notes modal on Escape key
document.addEventListener( 'keydown', function( e ) {
    if ( e.key === 'Escape' && notesAppliesToTable !== null ) {
        closeNotesModal() ;
    }
} ) ;
