package edu.tamucc.hri.griidc;

import edu.tamucc.hri.griidc.support.GriidcRisDepartmentMap;
import edu.tamucc.hri.griidc.support.GriidcRisInstitutionMap;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

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
