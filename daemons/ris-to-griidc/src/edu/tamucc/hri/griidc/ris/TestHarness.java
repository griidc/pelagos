package edu.tamucc.hri.griidc.ris;

import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.utils.GriidcRisDepartmentMap;
import edu.tamucc.hri.griidc.utils.GriidcRisInstitutionMap;

public class TestHarness {

	private GriidcRisDepartmentMap depMap = RdbmsUtils
			.getGriidcRisDepartmentMap();
	private GriidcRisInstitutionMap instMap = RdbmsUtils
			.getGriidcRisInstitutionMap();

	public TestHarness() {
	}

	public static void main(String[] args) {
		TestHarness th = new TestHarness();
		System.out.println(th.instMap.toString());
		System.out.println(th.depMap.toString());
	}

}
