package edu.tamucc.hri.griidc.test;

import org.ini4j.Ini;
import org.ini4j.Profile.Section;
import java.io.FileReader;

public class Ini4jTest {

	private static String dbIniFileName = "/Users/jvh/etc/griidc/db.ini";
	private static String notificationsFileName = "/Users/jvh/etc/griidc/notifications.ini";
	private static String[] fileName = { dbIniFileName, notificationsFileName };

	private static String RisIniSection = "RIS_RO";
	private static String GriidcIniSection = "GRIIDC_RW";
	private static String RisToGriidcNotifications = "ris-to-griidc";
	
	private static String[] notifyKeys = {
		"risErrors",
		"primaryLog",
		"risErrorsSender",
		"primaryLogSender"
	};

	public Ini4jTest() {
		// TODO Auto-generated constructor stub
	}

	public static void main(String[] args) throws Exception {
		for (String fn : fileName) {
			System.out.println("\nIni file " + fn);
			Ini ini = new Ini(new FileReader(fn));
			System.out.println("Number of sections: " + ini.size() + "\n");
			for (String sectionName : ini.keySet()) {
				System.out.println("[" + sectionName + "]");
				Section section = ini.get(sectionName);
				for (String optionKey : section.keySet()) {
					System.out.println("\t" + optionKey + "="
							+ section.get(optionKey));
				}
			}

			System.out.println("\nIni file " + dbIniFileName);
			ini = new Ini(new FileReader(dbIniFileName));
			System.out.println("Number of sections: " + ini.size() + "\n");
			for (String sectionName : ini.keySet()) {
				if (sectionName.equals(RisIniSection)
						|| sectionName.equals(GriidcIniSection)) {
					System.out.println("[" + sectionName + "]");
					Section section = ini.get(sectionName);
					for (String optionKey : section.keySet()) {
						System.out.println("\t" + optionKey + "="
								+ section.get(optionKey));
					}
				}
			}

			System.out.println("\nIni file " + notificationsFileName);
			ini = new Ini(new FileReader(notificationsFileName));
			System.out.println("Number of sections: " + ini.size() + "\n");
			for (String sectionName : ini.keySet()) {
				if (sectionName.equals(RisToGriidcNotifications)) {
					System.out.println("[" + sectionName + "]");
					Section section = ini.get(sectionName);
					String mailAddr = null;
					for(String k : notifyKeys) {
						mailAddr = section.get(k);
						System.out.println("\t" + k + " send to: " + mailAddr);
					}
					
				}
			}
		}

	}
}
