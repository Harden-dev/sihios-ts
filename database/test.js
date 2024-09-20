 // Fonction pour charger la liste avec Select2 et AJAX
            function chargerListeDynamique(selectedValue) {

                $.ajax({
                    url: "{{ route('get.lists.toFicheMetreForm') }}",
                    type: 'GET',
                    data: {
                        dataType: selectedValue
                    },
                    success: function(response) {
                        // Update the options in the Select2 dropdown with the fetched data
                        var newOptions = [];

                        $.each(response.data, function(index, item) {
                            newOptions.push({
                                id: item.id,
                                text: item.label
                            });
                        });

                        // Clear the existing options and add the new ones
                        var selectElement = $('select[name="' + selectedValue + '"]');

                        selectElement.empty().select2({
                            data: newOptions
                        });

                        // Get the selected ID from data-id attribute
                        var selectedId = selectElement.data('id');
                        if (selectedId > 0) selectElement.val(selectedId).trigger('change');
                        else selectElement.val(null).trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }