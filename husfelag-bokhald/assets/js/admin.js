jQuery(document).ready(function($) {
    // Staðfesta eyðingu
    $('.delete-confirm').on('click', function(e) {
        if (!confirm('Ertu viss um að þú viljir eyða þessu?')) {
            e.preventDefault();
        }
    });
    
    // Velja öll checkbox
    $('#select-all').on('change', function() {
        $('input[name="gjald_ids[]"]').prop('checked', this.checked);
    });
    
    // Uppfæra flokk þegar tegund breytist
    $('#tegund').on('change', function() {
        updateFlokkur();
    });
    
    function updateFlokkur() {
        const tegund = $('#tegund').val();
        const $flokkur = $('#flokkur');
        
        const flokkar = {
            'tekjur': ['Mánaðargjöld', 'Sérstök gjöld', 'Vextir', 'Annað'],
            'gjold': ['Viðhald', 'Þrif', 'Tryggingar', 'Rafmagn', 'Hiti', 'Vatn', 'Umsýsla', 'Annað']
        };
        
        $flokkur.empty().append('<option value="">Veldu flokk</option>');
        
        if (tegund && flokkar[tegund]) {
            $.each(flokkar[tegund], function(i, flokkur) {
                $flokkur.append('<option value="' + flokkur + '">' + flokkur + '</option>');
            });
        }
    }
    
    // Validation fyrir form
    $('form').on('submit', function(e) {
        const requiredFields = $(this).find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).css('border-color', '#dc3232');
                isValid = false;
            } else {
                $(this).css('border-color', '');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Vinsamlegast fylltu út öll nauðsynleg reitir.');
        }
    });
    
    // Númerareiknar fyrir upphæðir
    $('.amount-input').on('input', function() {
        const value = $(this).val().replace(/[^\d.,]/g, '');
        $(this).val(value);
    });
    
    // Tooltip fyrir hjálpartexta
    $('[data-tooltip]').on('mouseenter', function() {
        const tooltip = $('<div class="hb-tooltip">' + $(this).data('tooltip') + '</div>');
        $('body').append(tooltip);
        
        const pos = $(this).offset();
        tooltip.css({
            top: pos.top - tooltip.outerHeight() - 5,
            left: pos.left + ($(this).outerWidth() / 2) - (tooltip.outerWidth() / 2)
        });
    }).on('mouseleave', function() {
        $('.hb-tooltip').remove();
    });
});

// Prenta virkni
function printReport() {
    window.print();
}

// Export til CSV
function exportToCSV(tableId, filename) {
    const csv = [];
    const rows = document.querySelectorAll('#' + tableId + ' tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // Skip last column (actions)
            row.push(cols[j].innerText);
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
