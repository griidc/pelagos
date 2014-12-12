{% for key, tooltip in topicTooltips %}

	$("#topicKW{{instanceName}}{{key}}").qtip({
            content: {
				text: "{{tooltip}}"
			},
			position: {
				target: 'mouse',
				adjust: {
					x: 30
                }
			}
			
			/*,
			show: {
				target: $('#TOPKlist_{{instanceName}}, #TOPKselect_{{instanceName}}')
			},
			hide: {
				target: $('#TOPKlist_{{instanceName}}, #TOPKselect_{{instanceName}}')
			}*/
        });
		
{% endfor %}
