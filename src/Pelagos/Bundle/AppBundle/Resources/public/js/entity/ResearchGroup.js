$(document).ready(function()
{
    "use strict";

    $("#person").select2({
        placeholder: "[Please Select a Person]",
        allowClear: true,
        ajax: {
            dataType: 'json',
            data: function (params) {
                var query = {
                    "person.lastName": params.term + '*'
                }
                return query;
            },
            url: '/pelagos-symfony/dev/mvde/api/person-research-groups' +
                '?_properties=id,person.id,person.firstName,person.lastName,person.emailAddress',
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.person.lastName + ", " +  item.person.firstName + ", " + item.person.emailAddress,
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
            addOptionsByEntity(
                fundingCycle,
                "funding_cycles", "fundingOrganization=" + $(this).val()
            );
        }
    });

    // Set FundingCycle list back to match with the original funding organization
    $("form[entityType=\"ResearchGroup\"]").on("reset", function() {
        var fundingOrganization = $(this).find("[fundingOrganization]");
        var fundingOrganizationValue = fundingOrganization.attr("fundingOrganization");
        fundingOrganization.find("option").attr("selected", false);
        fundingOrganization.val(fundingOrganizationValue);
        fundingOrganization.find("[value=\"" + fundingOrganizationValue + "\"]").attr("selected", true);
        fundingOrganization.change();

        var fundingCycle = $(this).find("[fundingCycle]");
        var fundingCycleValue = fundingCycle.attr("fundingCycle");
        fundingCycle.val(fundingCycleValue);
        fundingCycle.find("option[value=\"" + fundingCycleValue + "\"]").attr("selected", true);
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
    "use strict";

    var url = $(selectElement).attr("data-url");
    if (typeof filter !== "undefined") {
        url += "?" + filter;// + "&properties=id,name";
    } else {
        //url += "?properties=id,name";
    }

    $.ajax({
        url: url,
        dataType: "json",
        async: false
    })
    .done(function(data) {
        var entities = sortObject(data, "name", false, true);

        $.each(entities, function(seq, item) {
            selectElement.append(
                $("<option></option>").val(item.id).html(item.name)
            );
        });
        //selectElement.find("option[value=\"" + selectElement.attr(entity) + "\"]").attr("selected", true);
    });
}
