$(document).ready(function()
{
    "use strict";

    $('[name="person"]', $('[name="person"]').parent(":not([newform])")).select2({
        placeholder: "[Please Select a Person]",
        allowClear: true,
        ajax: {
            dataType: "json",
            data: function (params) {
                if (params.term != undefined) {
                    var query = {
                        "lastName": params.term + "*"
                    }
                } else {
                    var query = {}
                }
                return query;
            },
            url: Routing.generate("pelagos_api_people_get_collection",
                {
                    "_properties" : "id,firstName,lastName,emailAddress",
                    "_orderBy" : "lastName,firstName,emailAddress"
                }
            ),
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.lastName + ", " +  item.firstName + ", " + item.emailAddress,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    //Disable RIS ID if not in create mode
    if ($("form[entityType=\"ResearchGroup\"] #id").val() !== "") {
        $("form[entityType=\"ResearchGroup\"] #id").attr("readonly",true);
    } else {
        var nextId = $("form[entityType=\"ResearchGroup\"] #id").attr("next-id");
        $("form[entityType=\"ResearchGroup\"] #id").val(nextId);
    }

    $("[fundingOrganization]").change(function () {
        var fundingCycle = $(this).nextAll("[fundingCycle]");
        fundingCycle.removeAttr("disabled")
            .find("option").remove();

        if ($(this).val() === "") {
            fundingCycle.attr("disabled", "disabled")
            .append("<option value=\"\">[Please Select a Funding Organization First]</option>");
        } else {
            fundingCycle.append("<option value=\"\">[Please Select a Funding Cycle]</option>");
            $.when(
                addOptionsByEntity(
                    fundingCycle,
                    "funding_cycles", "fundingOrganization=" + $(this).val()
                )
            ).then(function() {
                // Set FundingCycle list back to match with the original funding organization
                $("form[entityType=\"ResearchGroup\"]").on("reset", function() {
                    var fundingOrganization = $(this).find("[fundingOrganization]");
                    var fundingOrganizationValue = fundingOrganization.attr("fundingOrganization");
                    fundingOrganization.find("option").attr("selected", false);
                    fundingOrganization.val(fundingOrganizationValue);
                    fundingOrganization.find("[value=\"" + fundingOrganizationValue + "\"]").attr("selected", true);
                    fundingOrganization.change();

                    fundingCycle = $(this).find("[fundingCycle]");
                    var fundingCycleValue = fundingCycle.attr("fundingCycle");
                    fundingCycle.val(fundingCycleValue);
                    fundingCycle.find("option[value=\"" + fundingCycleValue + "\"]").attr("selected", true);
                });

                $("form[entityType=\"PersonResearchGroup\"]").on("reset", function() {
                    setTimeout(function () {
                        $('[name="person"]').change();
                    });
                });
            });
        }
    });
});

/**
 * This function add funding org options to a select element
 *
 * @param selectElement element Element of Select item.
 * @param entity string Name of the entity.
 * @param filter string Filter for the entity.
 *
 * @return void
 */
function addOptionsByEntity(selectElement, entity, filter)
{
    return $.Deferred(function() {
        var url = $(selectElement).attr("data-url");
        if (typeof filter !== "undefined") {
            url += "?" + filter;
        }

        $.getJSON(url, function(data) {
             var entities = sortObject(data, "name", false, true);

            $.each(entities, function(seq, item) {
                selectElement.append(
                    $("<option></option>").val(item.id).html(item.name)
                );
            });
        });

        this.resolve();
    });
}
